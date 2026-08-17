<?php

declare(strict_types=1);

namespace Core;

use JsonException;

final class HttpClient
{
    public function __construct(
        private readonly array $defaultHeaders = [],
        private readonly int $timeoutSeconds = 10,
        private readonly int $connectTimeoutSeconds = 5
    ) {
        if ($this->timeoutSeconds < 1 || $this->connectTimeoutSeconds < 1) {
            throw new HttpClientException('HTTP client timeouts must be at least one second.');
        }
    }

    public function get(string $url, array $query = [], array $headers = []): HttpResponse
    {
        return $this->request('GET', $url, [
            'query' => $query,
            'headers' => $headers,
        ]);
    }

    public function post(
        string $url,
        array $data = [],
        array $query = [],
        array $headers = []
    ): HttpResponse {
        return $this->sendJson('POST', $url, $data, $query, $headers);
    }

    public function put(
        string $url,
        array $data = [],
        array $query = [],
        array $headers = []
    ): HttpResponse {
        return $this->sendJson('PUT', $url, $data, $query, $headers);
    }

    public function patch(
        string $url,
        array $data = [],
        array $query = [],
        array $headers = []
    ): HttpResponse {
        return $this->sendJson('PATCH', $url, $data, $query, $headers);
    }

    public function delete(
        string $url,
        array $data = [],
        array $query = [],
        array $headers = []
    ): HttpResponse {
        return $this->sendJson('DELETE', $url, $data, $query, $headers);
    }

    private function sendJson(
        string $method,
        string $url,
        array $data,
        array $query,
        array $headers
    ): HttpResponse {
        return $this->request($method, $url, [
            'query' => $query,
            'headers' => $headers,
            'json' => $data,
        ]);
    }

    public function request(string $method, string $url, array $options = []): HttpResponse
    {
        if (array_key_exists('json', $options) && array_key_exists('body', $options)) {
            throw new HttpClientException('Choose either a JSON body or a raw body, not both.');
        }

        if (($options['query'] ?? []) !== []) {
            $separator = str_contains($url, '?') ? '&' : '?';
            $url .= $separator . http_build_query(
                $options['query'],
                '',
                '&',
                PHP_QUERY_RFC3986
            );
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (!in_array($scheme, ['http', 'https'], true)) {
            throw new HttpClientException('The request URL must use HTTP or HTTPS.');
        }

        if (!function_exists('curl_init')) {
            throw new HttpClientException('The PHP cURL extension is required.');
        }

        $handle = curl_init();
        if ($handle === false) {
            throw new HttpClientException('Unable to initialize cURL.');
        }

        $responseHeaders = [];
        $headers = array_merge($this->defaultHeaders, $options['headers'] ?? []);

        if (array_key_exists('json', $options)) {
            try {
                $options['body'] = json_encode(
                    $options['json'],
                    JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES
                );
            } catch (JsonException $exception) {
                curl_close($handle);

                throw new HttpClientException(
                    'Unable to encode the request body as JSON.',
                    0,
                    $exception
                );
            }

            $headers = ['Content-Type' => 'application/json', ...$headers];
        }

        $formattedHeaders = [];
        foreach ($headers as $name => $value) {
            $formattedHeaders[] = $name . ': ' . $value;
        }

        curl_setopt_array($handle, [
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $formattedHeaders,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeoutSeconds,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_MAXREDIRS => 0,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_USERAGENT => 'PHP-MVC-Starter/1.0',
            CURLOPT_HEADERFUNCTION => static function ($curl, string $line) use (&$responseHeaders): int {
                $length = strlen($line);
                $line = trim($line);

                if ($line === '' || !str_contains($line, ':')) {
                    return $length;
                }

                [$name, $value] = explode(':', $line, 2);
                $responseHeaders[strtolower(trim($name))][] = trim($value);

                return $length;
            },
        ]);

        if (array_key_exists('body', $options)) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $options['body']);
        }

        $body = curl_exec($handle);
        if ($body === false) {
            $message = curl_error($handle);
            $errorCode = curl_errno($handle);
            curl_close($handle);

            throw new HttpClientException('HTTP request failed: ' . $message, $errorCode);
        }

        $statusCode = (int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE);
        curl_close($handle);

        return new HttpResponse($statusCode, $responseHeaders, $body);
    }
}
