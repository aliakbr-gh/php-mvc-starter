<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$type = strtolower(trim((string) ($argv[1] ?? '')));
$input = trim(implode(' ', array_slice($argv, 2)));

if (!in_array($type, ['migration', 'controller', 'model'], true) || $input === '') {
    fwrite(
        STDERR,
        'Usage:' . PHP_EOL
        . '  php make migration <name>' . PHP_EOL
        . '  php make controller <name>' . PHP_EOL
        . '  php make model <name>' . PHP_EOL
    );
    exit(1);
}

$projectRoot = __DIR__;

/** Convert a user-supplied name to a safe snake_case identifier. */
$snakeName = static function (string $name): string {
    $name = preg_replace('/([a-z0-9])([A-Z])/', '$1_$2', trim($name)) ?? '';
    $name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name) ?? '');

    return trim($name, '_');
};

/** Convert a user-supplied name to a safe StudlyCase class name. */
$className = static function (string $name) use ($snakeName): string {
    $parts = array_filter(explode('_', $snakeName($name)));

    return implode('', array_map(static fn (string $part): string => ucfirst($part), $parts));
};

$writeFile = static function (string $path, string $contents): void {
    if (file_exists($path)) {
        throw new RuntimeException('File already exists: ' . $path);
    }

    $directory = dirname($path);

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create directory: ' . $directory);
    }

    if (file_put_contents($path, $contents, LOCK_EX) === false) {
        throw new RuntimeException('Unable to create file: ' . $path);
    }
};

try {
    if ($type === 'migration') {
        $name = $snakeName($input);

        if ($name === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
            throw new InvalidArgumentException('Migration names must begin with a letter.');
        }

        $filename = gmdate('Ymd_Hi') . '_' . $name . '.php';
        $path = $projectRoot . '/database/migrations/' . $filename;
        $contents = <<<'PHP'
<?php

declare(strict_types=1);

use Core\Migration;

return new class implements Migration {
    public function up(mysqli $database): void
    {
        // Apply the schema change.
    }

    public function down(mysqli $database): void
    {
        // Reverse the schema change.
    }
};
PHP;
        $writeFile($path, $contents . PHP_EOL);
    } else {
        $name = $className($input);

        if ($name === '' || !preg_match('/^[A-Z][A-Za-z0-9]*$/', $name)) {
            throw new InvalidArgumentException('Class names must begin with a letter.');
        }

        if ($type === 'controller') {
            $name = preg_replace('/Controller$/', '', $name) . 'Controller';
            $path = $projectRoot . '/app/Controllers/' . $name . '.php';
            $contents = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Controllers;\n\nuse Core\\Controller;\n\nfinal class {$name} extends Controller\n{\n}\n";
        } else {
            $path = $projectRoot . '/app/Models/' . $name . '.php';
            $contents = "<?php\n\ndeclare(strict_types=1);\n\nnamespace App\\Models;\n\nfinal class {$name}\n{\n}\n";
        }

        $writeFile($path, $contents);
    }

    echo 'Created: ' . substr($path, strlen($projectRoot) + 1) . PHP_EOL;
} catch (Throwable $exception) {
    fwrite(STDERR, 'Make failed: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}
