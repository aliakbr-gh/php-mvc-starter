<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

final class FileUploader
{
    public function upload(
        array $file,
        string $directory,
        array $allowedMimeTypes,
        int $maxBytes = 2_097_152
    ): string {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('The uploaded file is invalid.');
        }

        if ((int) ($file['size'] ?? 0) > $maxBytes) {
            throw new RuntimeException('The uploaded file is too large.');
        }

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        $mimeType = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        $extensions = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/x-icon' => 'ico',
            'image/vnd.microsoft.icon' => 'ico',
        ];

        if (!in_array($mimeType, $allowedMimeTypes, true) || !isset($extensions[$mimeType])) {
            throw new RuntimeException('The uploaded file type is not allowed.');
        }

        $relativeDirectory = 'uploads/' . trim($directory, '/');
        $absoluteDirectory = dirname(__DIR__) . '/public/' . $relativeDirectory;

        if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0775, true) && !is_dir($absoluteDirectory)) {
            throw new RuntimeException('Unable to create the upload directory.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
        $destination = $absoluteDirectory . '/' . $filename;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('Unable to store the uploaded file.');
        }

        return $relativeDirectory . '/' . $filename;
    }

    public function delete(?string $relativePath): void
    {
        if (!$relativePath || !str_starts_with($relativePath, 'uploads/')) {
            return;
        }

        $path = dirname(__DIR__) . '/public/' . $relativePath;

        if (is_file($path)) {
            unlink($path);
        }
    }
}
