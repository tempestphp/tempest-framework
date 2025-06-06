<?php

declare(strict_types=1);

namespace Tempest\Database\Connection;

use PDOStatement;

interface Connection
{
    public function beginTransaction(): bool;

    public function commit(): bool;

    public function rollback(): bool;

    public function lastInsertId(): false|string;

    public function prepare(string $sql): PDOStatement;

    public function close(): void;

    public function connect(): void;
}
