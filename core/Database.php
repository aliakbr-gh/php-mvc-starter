<?php

declare(strict_types=1);

namespace Core;

use mysqli;
use mysqli_sql_exception;

final class Database
{
    private static ?mysqli $connection = null;

    public static function connection(): mysqli
    {
        if (self::$connection instanceof mysqli) {
            return self::$connection;
        }

        $config = require dirname(__DIR__) . '/config/database.php';
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        try {
            self::$connection = new mysqli(
                $config['host'],
                $config['username'],
                $config['password'],
                $config['database'],
                $config['port']
            );
            self::$connection->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $exception) {
            self::$connection = null;

            throw new DatabaseConnectionException(
                'Unable to connect to the database.',
                (int) $exception->getCode(),
                $exception
            );
        }

        return self::$connection;
    }
}
