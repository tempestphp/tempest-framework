<?php

declare(strict_types=1);

namespace Tempest\Database;

use RuntimeException;
use Stringable;
use Tempest\Database\Drivers\MySqlDriver;
use Tempest\Database\Drivers\PostgreSqlDriver;
use Tempest\Database\Drivers\SQLiteDriver;
use UnhandledMatchError;

final class QueryStatement implements Stringable
{
    private string $table;

    public function __construct(
        private readonly DatabaseDriver $driver,
        private array                   $query = [],
    ) {
    }

    public static function new(DatabaseDriver $driver, string $table): self
    {
        $instance = new self($driver);
        $instance->table = $table;

        return $instance;
    }

    public function create(callable $callback): self
    {
        if (! empty($this->query)) {
            throw new RuntimeException('create statement should be the first statement');
        }

        $this->query[] = sprintf(
            "CREATE TABLE %s (%s);",
            $this->table,
            $callback(self::new($this->driver, $this->table))
        );

        return $this;
    }

    /** @throws UnhandledMatchError */
    public function alterTable(string $action, callable $callback): self
    {
        if (! empty($this->query)) {
            throw new RuntimeException('alter statement should be the first statement');
        }

        $operation = match ($this->driver::class) {
            MySqlDriver::class => sprintf('%s', strtoupper($action)),
            PostgreSqlDriver::class,
            SQLiteDriver::class => sprintf('%s COLUMN', strtoupper($action)),
        };

        $this->query[] = sprintf( // @coverage
            "ALTER TABLE %s %s %s",
            $this->table,
            $operation,
            $callback(self::new($this->driver, $this->table))
        );

        return $this;
    }

    public function primary($key = 'id'): self
    {
        $this->query[] = match ($this->driver::class) {
            MySqlDriver::class => sprintf('%s INTEGER PRIMARY KEY AUTO_INCREMENT', $key),
            PostgreSqlDriver::class => sprintf('%s SERIAL PRIMARY KEY', $key),
            default => sprintf('%s INTEGER PRIMARY KEY AUTOINCREMENT', $key),
        };

        return $this;
    }

    /** @throws UnhandledMatchError */
    public function constraint(string $localKey, string $table, string $key = 'id', string $onDelete = 'ON DELETE CASCADE', string $onUpdate = 'ON UPDATE NO ACTION'): self
    {
        $this->query[] = match ($this->driver::class) {
            MySqlDriver::class,
            PostgreSqlDriver::class => sprintf(
                'CONSTRAINT fk_%s_%s FOREIGN KEY (%s) REFERENCES %s(%s) %s %s',
                strtolower($table),
                strtolower($this->table),
                $localKey,
                $table,
                $key,
                $onDelete,
                $onUpdate
            ),
            SQLiteDriver::class => sprintf('FOREIGN KEY (%s) REFERENCES %s (%s) %s %s', $localKey, $table, $key, $onDelete, $onUpdate),
        };

        return $this;
    }

    public function statement(string $statement): self
    {
        $this->query[] = $statement;

        return $this;
    }

    public function __toString(): string
    {
        return implode(', ', $this->query);
    }

    public function toQuery(): Query
    {
        return new Query((string) $this);
    }
}
