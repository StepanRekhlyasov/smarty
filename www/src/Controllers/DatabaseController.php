<?php

namespace Smarty\Controllers;

use PDO;

final class DatabaseController
{
    private static ?PDO $connection = null;

    public function __construct(
        public readonly string $host,
        public readonly string $port,
        public readonly string $name,
        public readonly string $user,
        public readonly string $password,
    ) {
    }

    public static function fromEnvironment(): self
    {
        return new self(
            host: getenv('DB_HOST') ?: 'db',
            port: getenv('DB_PORT') ?: '3306',
            name: getenv('DB_NAME') ?: 'smarty',
            user: getenv('DB_USER') ?: 'smarty',
            password: getenv('DB_PASSWORD') ?: 'smarty',
        );
    }

    public function dsn(): string
    {
        return sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $this->host,
            $this->port,
            $this->name,
        );
    }

    public static function connect(): PDO
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $config = self::fromEnvironment();
        self::$connection = new PDO($config->dsn(), $config->user, $config->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);

        return self::$connection;
    }

    public static function db(): PDO
    {
        if (self::$connection === null) {
            throw new \RuntimeException('Database not initialized. Call DatabaseController::connect() in bootstrap.');
        }

        return self::$connection;
    }
}
