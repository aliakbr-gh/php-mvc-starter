<?php

declare(strict_types=1);

namespace App\Models;

use Core\Database;

final class APIClient
{
    public function findActiveByClientId(string $clientId): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, name, client_id, client_secret_hash, token_version
             FROM api_clients WHERE client_id = ? AND is_active = 1 LIMIT 1'
        );
        $statement->bind_param('s', $clientId);
        $statement->execute();
        $client = $statement->get_result()->fetch_assoc();

        if (!is_array($client)) {
            return null;
        }

        $client['id'] = (int) $client['id'];
        $client['token_version'] = (int) $client['token_version'];
        return $client;
    }

    public function recordAuthentication(int $id): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE api_clients SET last_authenticated_at = CURRENT_TIMESTAMP WHERE id = ?'
        );
        $statement->bind_param('i', $id);
        $statement->execute();
    }

    public function verifySecret(array $client, string $providedSecret): bool
    {
        $storedHash = (string) ($client['client_secret_hash'] ?? '');
        $providedHash = hash('sha256', $providedSecret);

        if (preg_match('/^[a-f0-9]{64}$/', $storedHash)) {
            return hash_equals($storedHash, $providedHash);
        }

        if (!password_verify($providedSecret, $storedHash)) {
            return false;
        }

        $this->upgradeSecretHash((int) $client['id'], $providedHash);
        return true;
    }

    private function upgradeSecretHash(int $id, string $secretHash): void
    {
        $statement = Database::connection()->prepare(
            'UPDATE api_clients SET client_secret_hash = ? WHERE id = ?'
        );
        $statement->bind_param('si', $secretHash, $id);
        $statement->execute();
    }
}
