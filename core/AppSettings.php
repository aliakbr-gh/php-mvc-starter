<?php

declare(strict_types=1);

namespace Core;

final class AppSettings
{
    private JsonStore $store;

    public function __construct()
    {
        $this->store = new JsonStore(
            dirname(__DIR__) . '/storage/config/app-settings.json',
            [
                'app_name' => (string) $GLOBALS['config']['name'],
                'logo' => '',
                'favicon' => '',
            ]
        );
    }

    public function get(): array
    {
        return $this->store->read();
    }

    public function save(array $settings): void
    {
        $this->store->update(
            static fn (array $current): array => array_replace($current, $settings)
        );
    }
}
