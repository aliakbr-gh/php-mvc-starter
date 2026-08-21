<?php

declare(strict_types=1);

namespace Core;

final class GoogleDriveSettings
{
    private JsonStore $store;
    private string $path;

    public function __construct()
    {
        $this->path = dirname(__DIR__) . '/storage/config/google-drive-settings.json';
        $this->store = new JsonStore($this->path, [
            'client_id' => '',
            'client_secret' => '',
            'refresh_token' => '',
            'folder_id' => '',
        ]);
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
            static fn (array $current): array => array_replace($current, $settings)
        );
        @chmod($this->path, 0600);
    }
}
