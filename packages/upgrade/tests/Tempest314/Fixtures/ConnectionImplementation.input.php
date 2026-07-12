<?php

namespace Tempest\Upgrade\Tests\Tempest314\Fixtures;

use PDOStatement;
use Tempest\Database\Connection\Connection;

final class CustomConnection implements Connection
{
    public function beginTransaction(): bool
    {
        return false;
    }

    public function commit(): bool
    {
        return false;
    }

    public function rollback(): bool
    {
        return false;
    }

    public function lastInsertId(): false|string
    {
        return false;
    }

    public function prepare(string $sql): PDOStatement
    {
        return new PDOStatement();
    }

    public function close(): void
    {
    }

    public function connect(): void
    {
    }
}
