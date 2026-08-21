<?php

declare(strict_types=1);

namespace Core;

use JsonException;
use RuntimeException;

final class GoogleDriveService
{
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const UPLOAD_URL = 'https://www.googleapis.com/upload/drive/v3/files';
    private const SCOPE = 'https://www.googleapis.com/auth/drive.file';

    public function __construct(private readonly GoogleDriveSettings $store = new GoogleDriveSettings())
    {
    }

    public function authorizationUrl(string $redirectUri, string $state): string
    {
        $settings = $this->settingsWithClient();

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $settings['client_id'],
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => self::SCOPE,
            'access_type' => 'offline',
            'prompt' => 'consent',
            'include_granted_scopes' => 'true',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function connect(string $code, string $redirectUri): void
    {
        $settings = $this->settingsWithClient();
        $token = $this->tokenRequest([
            'code' => $code,
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);
        $refreshToken = trim((string) ($token['refresh_token'] ?? ''));

        if ($refreshToken === '') {
            throw new RuntimeException('Google did not return an offline refresh token. Reconnect and grant access.');
        }

        $this->store->save(['refresh_token' => $refreshToken]);
    }

    /** @return array{id: string, name: string, webViewLink: string|null} */
    public function upload(string $path, string $filename, string $mimeType): array
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('The backup file is not available for upload.');
        }

        $settings = $this->settingsWithClient();
        $refreshToken = trim((string) ($settings['refresh_token'] ?? ''));

        if ($refreshToken === '') {
            throw new RuntimeException('Google Drive is not connected.');
        }

        $token = $this->tokenRequest([
            'client_id' => $settings['client_id'],
            'client_secret' => $settings['client_secret'],
            'refresh_token' => $refreshToken,
            'grant_type' => 'refresh_token',
        ]);
        $accessToken = trim((string) ($token['access_token'] ?? ''));

        if ($accessToken === '') {
            throw new RuntimeException('Google did not return an access token.');
        }

        $metadata = ['name' => $filename];
        $folderId = trim((string) ($settings['folder_id'] ?? ''));
        if ($folderId !== '') {
            $metadata['parents'] = [$folderId];
        }

        $size = filesize($path);
        if ($size === false) {
            throw new RuntimeException('Unable to determine the backup file size.');
        }

        $sessionUrl = $this->startResumableUpload($metadata, $mimeType, $size, $accessToken);

        return $this->streamFile($sessionUrl, $path, $mimeType);
    }

    private function settingsWithClient(): array
    {
        $settings = $this->store->get();

        if (trim((string) ($settings['client_id'] ?? '')) === ''
            || trim((string) ($settings['client_secret'] ?? '')) === ''
        ) {
            throw new RuntimeException('Configure the Google OAuth client ID and client secret first.');
        }

        return $settings;
    }

    private function tokenRequest(array $parameters): array
    {
        $response = (new HttpClient([], 30, 10))->request('POST', self::TOKEN_URL, [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'body' => http_build_query($parameters, '', '&', PHP_QUERY_RFC3986),
        ]);

        if (!$response->successful()) {
            throw new RuntimeException($this->googleError($response->body(), 'Google authorization failed.'));
        }

        try {
            return $response->json();
        } catch (JsonException $exception) {
            throw new RuntimeException('Google returned an invalid authorization response.', 0, $exception);
        }
    }

    private function startResumableUpload(
        array $metadata,
        string $mimeType,
        int $size,
        string $accessToken
    ): string {
        $response = (new HttpClient([], 30, 10))->request('POST', self::UPLOAD_URL, [
            'query' => [
                'uploadType' => 'resumable',
                'supportsAllDrives' => 'true',
                'fields' => 'id,name,webViewLink',
            ],
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json; charset=UTF-8',
                'X-Upload-Content-Type' => $mimeType,
                'X-Upload-Content-Length' => (string) $size,
            ],
            'json' => $metadata,
        ]);

        $location = $response->header('location');
        if (!$response->successful() || $location === null || $location === '') {
            throw new RuntimeException($this->googleError($response->body(), 'Google Drive rejected the upload.'));
        }

        return $location;
    }

    private function streamFile(string $sessionUrl, string $path, string $mimeType): array
    {
        if (!function_exists('curl_init')) {
            throw new RuntimeException('The PHP cURL extension is required for Google Drive uploads.');
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new RuntimeException('Unable to read the generated backup.');
        }

        $curl = curl_init($sessionUrl);
        if ($curl === false) {
            fclose($handle);
            throw new RuntimeException('Unable to initialize the Google Drive upload.');
        }

        $size = filesize($path);
        if ($size === false) {
            curl_close($curl);
            fclose($handle);
            throw new RuntimeException('Unable to determine the backup file size.');
        }

        curl_setopt_array($curl, [
            CURLOPT_UPLOAD => true,
            CURLOPT_INFILE => $handle,
            CURLOPT_INFILESIZE => $size,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: ' . $mimeType],
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTPS,
        ]);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        fclose($handle);

        if ($body === false || $status < 200 || $status >= 300) {
            throw new RuntimeException(
                $body === false
                    ? 'Google Drive upload failed: ' . $error
                    : $this->googleError($body, 'Google Drive upload failed.')
            );
        }

        try {
            $file = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Google Drive returned invalid file metadata.', 0, $exception);
        }

        return [
            'id' => (string) ($file['id'] ?? ''),
            'name' => (string) ($file['name'] ?? ''),
            'webViewLink' => isset($file['webViewLink']) ? (string) $file['webViewLink'] : null,
        ];
    }

    private function googleError(string $body, string $fallback): string
    {
        try {
            $decoded = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
            $error = $decoded['error'] ?? null;
            $message = is_array($error)
                ? ($error['message'] ?? null)
                : ($decoded['error_description'] ?? (is_string($error) ? $error : null));

            return is_string($message) && $message !== '' ? $message : $fallback;
        } catch (JsonException) {
            return $fallback;
        }
    }
}
