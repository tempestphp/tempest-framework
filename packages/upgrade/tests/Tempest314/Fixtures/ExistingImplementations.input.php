<?php

namespace Tempest\Upgrade\Tests\Tempest314\Fixtures;

use PDOStatement;
use Tempest\Container\Container;
use Tempest\Core\Kernel;
use Tempest\Database\Connection\Connection;

final class ExistingKernel implements Kernel
{
    public string $root;

    public string $internalStorage;

    public Container $container;

    public bool $wasReset = false;

    public bool $wasShutDown = false;

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
        $this->wasShutDown = true;
    }
}

final class ExistingConnection implements Connection
{
    public function beginTransaction(): bool
    {
        return true;
    }

    public function inTransaction(): bool
    {
        return true;
    }

    public function commit(): bool
    {
        return true;
    }

    public function rollback(): bool
    {
        return true;
    }

    public function lastInsertId(): false|string
    {
        return 'existing-id';
    }

    public function prepare(string $sql): PDOStatement
    {
        return new PDOStatement();
    }

    public function close(): void
    {
        $this->disconnect();
    }

    public function connect(): void
    {
        $this->bootConnection();
    }

    public function reconnect(): void
    {
        $this->close();
        $this->connect();
    }

    public function ping(): bool
    {
        return true;
    }

    private function disconnect(): void
    {
    }

    private function bootConnection(): void
    {
    }
}
