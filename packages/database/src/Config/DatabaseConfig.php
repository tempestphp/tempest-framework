<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use Tempest\Container\HasTag;
use Tempest\Database\Migrations\MigrationNamingStrategy;
use Tempest\Database\Tables\NamingStrategy;

interface DatabaseConfig extends HasTag
{
    /**
     * PDO data source name connection string.
     */
    public string $dsn {
        get;
    }

    /**
     * The naming strategy for database tables and columns.
     */
    public NamingStrategy $namingStrategy {
        get;
    }

    /**
     * The naming strategy for migration file prefixes.
     */
    public MigrationNamingStrategy $migrationNamingStrategy {
        get;
    }

    /**
     * The database dialect (MySQL, PostgreSQL, SQLite).
     */
    public DatabaseDialect $dialect {
        get;
    }

    /**
     * The database username for authentication.
     */
    public ?string $username {
        get;
    }

    /**
     * The database password for authentication.
     */
    public ?string $password {
        get;
    }

    /**
     * Whether to use persistent database connections.
     */
    public bool $usePersistentConnection {
        get;
    }

    /**
     * PDO connection options built from configuration properties.
     */
    public array $options {
        get;
    }
}
