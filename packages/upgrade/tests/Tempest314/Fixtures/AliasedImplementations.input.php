<?php

namespace Tempest\Upgrade\Tests\Tempest314\Fixtures;

use PDOStatement;
use Tempest\Container\Container;
use Tempest\Core\Kernel as TempestKernel;
use Tempest\Database\Connection\Connection as TempestConnection;

final class AliasedKernel implements TempestKernel
{
    public string $root;

    public string $internalStorage;

    public Container $container;

    public static function boot(
        string $root,
        array $discoveryLocations = [],
        ?Container $container = null,
        ?string $internalStorage = null,
    ): self {
        return new self();
    }

    public function shutdown(int|string $status = ''): void
    {
        return $this;
    }
}

final class AliasedConnection implements TempestConnection
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
