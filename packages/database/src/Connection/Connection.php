<?php

declare(strict_types=1);

namespace Tempest\Database\Connection;

use PDOStatement;
use Tempest\Database\Config\DatabaseConfig;

interface Connection
{
    public DatabaseConfig $config {
        get;
    }

    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollback(): bool;

    public function lastInsertId(): false|string;

    public function prepare(string $sql): PDOStatement;

    public function close(): void;

    public function connect(): void;
}
