<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Drivers\MySqlDriver;
use Tempest\Database\Drivers\PostgreSqlDriver;
use Tempest\Database\Drivers\SQLiteDriver;

final class DatabaseDriverFactory
{
    public static function make(DatabaseDialect $dialect, array $options): DatabaseDriver
    {
        $instance = new self();

        return match ($dialect) {
            DatabaseDialect::MYSQL => new MySqlDriver(...array_filter($instance->formatOptions($dialect, $options))),
            DatabaseDialect::POSTGRESQL => new PostgreSqlDriver(...array_filter($instance->formatOptions($dialect, $options))),
            DatabaseDialect::SQLITE => new SQLiteDriver(...array_filter($instance->formatOptions($dialect, $options))),
        };
    }

    private function formatOptions(DatabaseDialect $dialect, array $options): array
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL,
            DatabaseDialect::POSTGRESQL => [
                'host' => $options['host'] ?? null,
                'port' => $options['port'] ?? null,
                'username' => $options['username'] ?? null,
                'password' => $options['password'] ?? null,
                'database' => $options['database'] ?? null,
            ],
            DatabaseDialect::SQLITE => [
                'path' => $options['path'] ?? null,
            ],
        };
    }
}
