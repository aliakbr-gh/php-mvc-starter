<?php

declare(strict_types=1);

namespace Core;

final class EmailSettings
{
    private JsonStore $store;
    private string $path;

    public function __construct()
    {
        $this->path = dirname(__DIR__) . '/storage/config/email-settings.json';
        $this->store = new JsonStore(
            $this->path,
            [
                'active_transport' => 'gmail',
                'gmail' => [
                    'enabled' => false,
                    'email' => '',
                    'app_password' => '',
                    'from_name' => '',
                ],
                'smtp' => [
                    'enabled' => false,
                    'host' => '',
                    'port' => 587,
                    'encryption' => 'tls',
                    'username' => '',
                    'password' => '',
                    'from_email' => '',
                    'from_name' => '',
                ],
            ]
        );
    }

    public function get(): array
    {
        $settings = $this->store->read();
        @chmod($this->path, 0600);

        return $settings;
    }

    public function save(array $settings): void
    {
        $this->store->update(
            static fn (array $current): array => array_replace_recursive($current, $settings)
        );
        @chmod($this->path, 0600);
    }
}
