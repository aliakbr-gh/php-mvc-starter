<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    private static ?PDO $connection = null;

    protected function db(): PDO
    {
        if (self::$connection === null) {
            $config = require BASE_PATH . '/config/database.php';
            self::$connection = new PDO($config['dsn'], $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$connection;
    }
}
