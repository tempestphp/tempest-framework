<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use PDO;
use SensitiveParameter;
use Tempest\Database\Exceptions\DatabaseConnectionStringWasInvalid;
use Tempest\Database\Migrations\DatePrefixStrategy;
use Tempest\Database\Migrations\MigrationNamingStrategy;
use Tempest\Database\Tables\NamingStrategy;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;
use UnitEnum;

final class SQLiteConfig implements DatabaseConfig
{
    public string $dsn {
        get => sprintf(
            'sqlite:%s',
            $this->path,
        );
    }

    public ?string $username {
        get => null;
    }

    public ?string $password {
        get => null;
    }

    public DatabaseDialect $dialect {
        get => DatabaseDialect::SQLITE;
    }

    public bool $usePersistentConnection {
        get => $this->persistent;
    }

    public MigrationNamingStrategy $migrationNamingStrategy {
        get => $this->migrationNaming;
    }

    public array $options {
        get {
            $options = [];

            if ($this->persistent) {
                $options[PDO::ATTR_PERSISTENT] = true;
            }

            return $options;
        }
    }

    /**
     * @param string $path Path to the SQLite database file. Use ':memory:' for an in-memory database.
     * @param bool $persistent Whether to use persistent connections. Persistent connections are not closed at the end of the script and are cached for reuse when another script requests a connection using the same credentials.
     * @param NamingStrategy $namingStrategy The naming strategy for database tables and columns.
     * @param MigrationNamingStrategy $migrationNaming The naming strategy for migration file prefixes.
     * @param string|UnitEnum|null $tag An optional tag to identify this database configuration.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $path = 'localhost',
        public bool $persistent = false,
        public NamingStrategy $namingStrategy = new PluralizedSnakeCaseStrategy(),
        public MigrationNamingStrategy $migrationNaming = new DatePrefixStrategy(),
        public string|UnitEnum|null $tag = null,
    ) {}

    /**
     * Creates a SQLite configuration from a connection string, such as `sqlite:///path/to/database.sqlite` or `sqlite::memory:`.
     *
     * @param string $connectionString The connection string, using the `sqlite` scheme.
     * @param mixed ...$options Named constructor arguments, which take precedence over the values parsed from the connection string.
     */
    public static function fromConnectionString(#[SensitiveParameter] string $connectionString, mixed ...$options): self
    {
        $parsed = DatabaseConnectionString::from($connectionString);

        if ($parsed->scheme !== 'sqlite') {
            throw DatabaseConnectionStringWasInvalid::schemeWasNotSupported($parsed->scheme, ['sqlite']);
        }

        if ($parsed->path === null) {
            throw DatabaseConnectionStringWasInvalid::couldNotBeParsed();
        }

        return new self(...[
            'path' => $parsed->path,
            ...$options,
        ]);
    }
}
