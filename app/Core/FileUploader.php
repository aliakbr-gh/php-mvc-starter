<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class FileUploader
{
    public function __construct(
        private readonly string $directory,
        private readonly string $publicPath,
    ) {
    }

    public function upload(array $file, array $allowedTypes, int $maxBytes = 2097152): ?string
    {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);

        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($error !== UPLOAD_ERR_OK) {
            throw new RuntimeException($this->uploadError($error));
        }

        $temporaryPath = (string) ($file['tmp_name'] ?? '');
        $size = (int) ($file['size'] ?? 0);

        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            throw new RuntimeException('The uploaded file is invalid.');
        }

        if ($size < 1 || $size > $maxBytes) {
            throw new RuntimeException('The uploaded file must not exceed ' . $this->formatBytes($maxBytes) . '.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($temporaryPath);
        if (!is_string($mime) || !isset($allowedTypes[$mime])) {
            throw new RuntimeException('The uploaded file type is not supported.');
        }

        if (!is_dir($this->directory)
            && !mkdir($this->directory, 0775, true)
            && !is_dir($this->directory)) {
            throw new RuntimeException('Could not create the upload directory.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $allowedTypes[$mime];
        $destination = rtrim($this->directory, '/') . '/' . $filename;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('Could not store the uploaded file.');
        }

        return trim($this->publicPath, '/') . '/' . $filename;
    }

    private function uploadError(int $error): string
    {
        return match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The uploaded file is too large.',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'The upload temporary directory is missing.',
            UPLOAD_ERR_CANT_WRITE => 'The server could not write the uploaded file.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'The file upload failed.',
        };
    }

    private function formatBytes(int $bytes): string
    {
        return $bytes >= 1048576
            ? rtrim(rtrim(number_format($bytes / 1048576, 1), '0'), '.') . ' MB'
            : (string) ceil($bytes / 1024) . ' KB';
    }
}
