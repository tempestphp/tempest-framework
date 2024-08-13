<?php

declare(strict_types=1);

namespace Tempest\Database;

use Tempest\Database\Drivers\MySqlDriver;
use Tempest\Database\Drivers\PostgreSqlDriver;
use Tempest\Database\Drivers\SQLiteDriver;

final class DatabaseFactory
{
    public static function make(DatabaseDialect $dialect, array $options): DatabaseConfig
    {
        $instance = new self();

        return new DatabaseConfig(
            match ($dialect) {
                DatabaseDialect::MYSQL => new MySqlDriver(...$instance->formatOptions($dialect, $options)),
                DatabaseDialect::POSTGRESQL => new PostgreSqlDriver(...$instance->formatOptions($dialect, $options)),
                DatabaseDialect::SQLITE => new SQLiteDriver(...$instance->formatOptions($dialect, $options)),
            },
        );
    }

    private function formatOptions(DatabaseDialect $dialect, array $options): array
    {
        return match ($dialect) {
            DatabaseDialect::MYSQL,
            DatabaseDialect::POSTGRESQL => [
                'host' => $options['host'],
                'port' => $options['port'],
                'username' => $options['username'],
                'password' => $options['password'],
                'database' => $options['database'],
            ],
            DatabaseDialect::SQLITE => ['path' => $options['path']],
        };
    }
}
