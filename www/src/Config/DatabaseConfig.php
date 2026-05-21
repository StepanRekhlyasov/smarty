<?php

namespace Smarty\Config;

use PDO;

final class DatabaseConfig
{
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
        return new PDO(self::fromEnvironment()->dsn(), self::fromEnvironment()->user, self::fromEnvironment()->password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]);
    }
}
