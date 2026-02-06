<?php

declare(strict_types=1);

class Database
{
    private static ?PDO $connection = null;

    public static function connect(array $config): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $driver = $config['driver'] ?? '';

        if ($driver === 'sqlite') {
            $databasePath = $config['sqlite']['path'] ?? '';
            if ($databasePath === '') {
                throw new RuntimeException('SQLite database path is missing from config.');
            }
            $dsn = 'sqlite:' . $databasePath;
            self::$connection = new PDO($dsn);
        } elseif ($driver === 'mysql') {
            $mysql = $config['mysql'] ?? [];
            $host = $mysql['host'] ?? '127.0.0.1';
            $port = (string) ($mysql['port'] ?? '3306');
            $database = $mysql['database'] ?? '';
            $username = $mysql['username'] ?? '';
            $password = $mysql['password'] ?? '';

            if ($database === '' || $username === '') {
                throw new RuntimeException('MySQL configuration is incomplete.');
            }

            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $database);
            self::$connection = new PDO($dsn, $username, $password);
        } else {
            throw new RuntimeException('Unsupported database driver.');
        }

        self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        self::$connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        return self::$connection;
    }
}
