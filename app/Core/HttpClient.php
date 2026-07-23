<?php

declare(strict_types=1);

namespace App\Core;

use JsonException;
use RuntimeException;

final class HttpClient
{
    public function __construct(
        private readonly array $defaultHeaders = [],
        private readonly int $timeout = 30,
        private readonly int $connectTimeout = 10,
    ) {
    }

    public function get(string $url, array $options = []): HttpResponse
    {
        return $this->request('GET', $url, $options);
    }

    public function post(string $url, array $options = []): HttpResponse
    {
        return $this->request('POST', $url, $options);
    }

    public function put(string $url, array $options = []): HttpResponse
    {
        return $this->request('PUT', $url, $options);
    }

    public function delete(string $url, array $options = []): HttpResponse
    {
        return $this->request('DELETE', $url, $options);
    }

    public function request(string $method, string $url, array $options = []): HttpResponse
    {
        if (!extension_loaded('curl')) {
            throw new RuntimeException('The cURL PHP extension is required by HttpClient.');
        }

        $url = $this->withQuery($url, $options['query'] ?? []);
        $headers = array_merge($this->defaultHeaders, $options['headers'] ?? []);
        $responseHeaders = [];
        $curl = curl_init($url);

        if ($curl === false) {
            throw new RuntimeException('Could not initialize the HTTP client.');
        }

        $curlOptions = [
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => (int) ($options['timeout'] ?? $this->timeout),
            CURLOPT_CONNECTTIMEOUT => (int) ($options['connect_timeout'] ?? $this->connectTimeout),
            CURLOPT_FOLLOWLOCATION => (bool) ($options['follow_redirects'] ?? false),
            CURLOPT_MAXREDIRS => (int) ($options['max_redirects'] ?? 5),
            CURLOPT_SSL_VERIFYPEER => (bool) ($options['verify'] ?? true),
            CURLOPT_SSL_VERIFYHOST => ($options['verify'] ?? true) ? 2 : 0,
            CURLOPT_HTTPHEADER => $this->formatHeaders($headers),
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $line = trim($line);

                if ($line === '' || str_starts_with($line, 'HTTP/')) {
                    if (str_starts_with($line, 'HTTP/')) {
                        $responseHeaders = [];
                    }
                    return $length;
                }

                if (str_contains($line, ':')) {
                    [$name, $value] = explode(':', $line, 2);
                    $responseHeaders[strtolower(trim($name))] = trim($value);
                }

                return $length;
            },
        ];

        if (array_key_exists('json', $options)) {
            try {
                $curlOptions[CURLOPT_POSTFIELDS] = json_encode($options['json'], JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new RuntimeException('Could not encode the HTTP JSON body.', 0, $exception);
            }
            $headers['Content-Type'] ??= 'application/json';
            $headers['Accept'] ??= 'application/json';
            $curlOptions[CURLOPT_HTTPHEADER] = $this->formatHeaders($headers);
        } elseif (array_key_exists('form', $options)) {
            $curlOptions[CURLOPT_POSTFIELDS] = http_build_query($options['form']);
            $headers['Content-Type'] ??= 'application/x-www-form-urlencoded';
            $curlOptions[CURLOPT_HTTPHEADER] = $this->formatHeaders($headers);
        } elseif (array_key_exists('body', $options)) {
            $curlOptions[CURLOPT_POSTFIELDS] = (string) $options['body'];
        }

        if (isset($options['basic_auth'])) {
            $credentials = $options['basic_auth'];
            if (!is_array($credentials) || count($credentials) !== 2) {
                throw new RuntimeException('The basic_auth option must contain a username and password.');
            }
            $curlOptions[CURLOPT_USERPWD] = (string) $credentials[0] . ':' . (string) $credentials[1];
        }

        curl_setopt_array($curl, $curlOptions);
        $body = curl_exec($curl);

        if ($body === false) {
            $message = curl_error($curl);
            $code = curl_errno($curl);
            curl_close($curl);
            throw new RuntimeException('HTTP request failed: ' . $message, $code);
        }

        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        return new HttpResponse($status, $responseHeaders, $body);
    }

    private function withQuery(string $url, mixed $query): string
    {
        if (!is_array($query) || $query === []) {
            return $url;
        }

        return $url . (str_contains($url, '?') ? '&' : '?') . http_build_query($query);
    }

    private function formatHeaders(array $headers): array
    {
        $formatted = [];

        foreach ($headers as $name => $value) {
            $formatted[] = is_int($name) ? (string) $value : $name . ': ' . $value;
        }

        return $formatted;
    }
}
