<?php

declare(strict_types=1);

namespace Tempest\Database\Connection;

use PDO;
use PDOStatement;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Exceptions\ConnectionClosed;
use Throwable;

final class PDOConnection implements Connection
{
    private ?PDO $pdo = null;

    public function __construct(
        private(set) readonly DatabaseConfig $config,
    ) {}

    public function beginTransaction(): bool
    {
        if ($this->pdo === null) {
            throw new ConnectionClosed();
        }

        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        if ($this->pdo === null) {
            throw new ConnectionClosed();
        }

        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        if ($this->pdo === null) {
            throw new ConnectionClosed();
        }

        return $this->pdo->rollBack();
    }

    public function lastInsertId(): false|string
    {
        if ($this->pdo === null) {
            throw new ConnectionClosed();
        }

        return $this->pdo->lastInsertId();
    }

    public function prepare(string $sql): PDOStatement
    {
        if ($this->pdo === null) {
            throw new ConnectionClosed();
        }

        $statement = $this->pdo->prepare($sql);

        if ($statement === false) {
            throw new ConnectionClosed();
        }

        return $statement;
    }

    public function ping(): bool
    {
        try {
            $statement = $this->prepare('SELECT 1');
            $statement->execute();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function reconnect(): void
    {
        $this->close();
        $this->connect();
    }

    public function close(): void
    {
        $this->pdo = null;
    }

    public function connect(): void
    {
        if ($this->pdo !== null) {
            return;
        }

        $this->pdo = new PDO(
            dsn: $this->config->dsn,
            username: $this->config->username,
            password: $this->config->password,
            options: $this->config->options,
        );
    }
}
