<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

final class WhatsAppService
{
    public function messageUrl(string $receiverNumber, string $message): string
    {
        $number = preg_replace('/\D+/', '', trim($receiverNumber)) ?? '';
        $message = trim($message);

        if (strlen($number) < 7 || strlen($number) > 15) {
            throw new RuntimeException('Enter the receiver number in international format, including country code.');
        }
        if ($message === '' || strlen($message) > 4096) {
            throw new RuntimeException('Message must be between 1 and 4096 characters.');
        }

        return 'https://wa.me/' . $number . '?text=' . rawurlencode($message);
    }
}
