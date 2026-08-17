<?php

declare(strict_types=1);

namespace Core\API;

use JsonException;
use RuntimeException;

final class JWT
{
    public static function encode(array $claims, string $secret): string
    {
        self::assertSecret($secret);
        $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'HS256'], JSON_THROW_ON_ERROR));
        $payload = self::base64UrlEncode(json_encode($claims, JSON_THROW_ON_ERROR));
        $signature = hash_hmac('sha256', $header . '.' . $payload, $secret, true);

        return $header . '.' . $payload . '.' . self::base64UrlEncode($signature);
    }

    public static function decode(string $token, string $secret, string $issuer, string $audience): array
    {
        self::assertSecret($secret);
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Malformed JWT.');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;
        try {
            $header = json_decode(self::base64UrlDecode($encodedHeader), true, 512, JSON_THROW_ON_ERROR);
            $claims = json_decode(self::base64UrlDecode($encodedPayload), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('Malformed JWT.');
        }

        if (!is_array($header) || ($header['alg'] ?? null) !== 'HS256' || ($header['typ'] ?? null) !== 'JWT') {
            throw new RuntimeException('Unsupported JWT header.');
        }

        $signature = self::base64UrlDecode($encodedSignature);
        $expected = hash_hmac('sha256', $encodedHeader . '.' . $encodedPayload, $secret, true);
        if (!hash_equals($expected, $signature)) {
            throw new RuntimeException('Invalid JWT signature.');
        }

        if (!is_array($claims)) {
            throw new RuntimeException('Invalid JWT claims.');
        }

        $now = time();
        if (($claims['iss'] ?? null) !== $issuer || ($claims['aud'] ?? null) !== $audience) {
            throw new RuntimeException('Invalid JWT issuer or audience.');
        }
        if (!isset($claims['sub'], $claims['iat'], $claims['nbf'], $claims['exp'], $claims['jti'])) {
            throw new RuntimeException('Required JWT claims are missing.');
        }
        if ((int) $claims['nbf'] > $now || (int) $claims['iat'] > $now + 30 || (int) $claims['exp'] <= $now) {
            throw new RuntimeException('JWT is not currently valid.');
        }

        return $claims;
    }

    private static function assertSecret(string $secret): void
    {
        if (strlen($secret) < 32) {
            throw new RuntimeException('APP_JWT_SECRET must contain at least 32 characters.');
        }
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string
    {
        if ($value === '' || preg_match('/[^A-Za-z0-9_-]/', $value)) {
            throw new RuntimeException('Invalid Base64URL value.');
        }

        $padding = (4 - strlen($value) % 4) % 4;
        $decoded = base64_decode(strtr($value . str_repeat('=', $padding), '-_', '+/'), true);
        if ($decoded === false) {
            throw new RuntimeException('Invalid Base64URL value.');
        }

        return $decoded;
    }
}
