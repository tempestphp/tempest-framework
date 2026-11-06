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

final class PostgresConfig implements DatabaseConfig
{
    public string $dsn {
        get => sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s%s',
            $this->host,
            $this->port,
            $this->database,
            $this->username,
            $this->password,
            implode('', array_map(
                fn (string $key, string $value) => ";{$key}={$value}",
                array_keys($this->parameters),
                $this->parameters,
            )),
        );
    }

    public DatabaseDialect $dialect {
        get => DatabaseDialect::POSTGRESQL;
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
     * @param string $host The PostgreSQL server hostname or IP address, or the directory containing its Unix socket.
     * @param string $port The PostgreSQL server port number.
     * @param string $username The PostgreSQL username for authentication.
     * @param string $password The PostgreSQL password for authentication.
     * @param string $database The database name to connect to.
     * @param bool $persistent Whether to use persistent connections. Persistent connections are not closed at the end of the script and are cached for reuse when another script requests a connection using the same credentials.
     * @param NamingStrategy $namingStrategy The naming strategy for database tables and columns.
     * @param MigrationNamingStrategy $migrationNaming The naming strategy for migration file prefixes.
     * @param string|UnitEnum|null $tag An optional tag to identify this database configuration.
     * @param array<string,string> $parameters Additional parameters appended to the DSN, such as `sslmode`, `connect_timeout` or `keepalives`.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $host = '127.0.0.1',
        #[SensitiveParameter]
        public string $port = '5432',
        #[SensitiveParameter]
        public string $username = 'postgres',
        #[SensitiveParameter]
        public string $password = '',
        #[SensitiveParameter]
        public string $database = 'app',
        public bool $persistent = false,
        public NamingStrategy $namingStrategy = new PluralizedSnakeCaseStrategy(),
        public MigrationNamingStrategy $migrationNaming = new DatePrefixStrategy(),
        public string|UnitEnum|null $tag = null,
        public array $parameters = [],
    ) {}

    /**
     * Creates a PostgreSQL configuration from a connection string, such as `postgres://user:password@host:5432/database?sslmode=require`. Components missing from the connection string fall back to their defaults, and query parameters are appended to the DSN. As with libpq, the `host`, `port`, `user`, `password` and `dbname` query parameters take precedence over the other components, so a Unix socket may be given as `postgres:///database?host=/var/run/postgresql` or as a percent-encoded host.
     *
     * @param string $connectionString The connection string. Supported schemes are `postgres`, `postgresql` and `pgsql`.
     * @param mixed ...$options Named constructor arguments, which take precedence over the values parsed from the connection string.
     */
    public static function fromConnectionString(#[SensitiveParameter] string $connectionString, mixed ...$options): self
    {
        $parsed = DatabaseConnectionString::from($connectionString);

        if (! in_array($parsed->scheme, ['postgres', 'postgresql', 'pgsql'], strict: true)) {
            throw DatabaseConnectionStringWasInvalid::schemeWasNotSupported($parsed->scheme, ['postgres', 'postgresql', 'pgsql']);
        }

        $parameters = $parsed->parameters;

        $arguments = [
            'host' => $parameters['host'] ?? $parsed->host,
            'port' => $parameters['port'] ?? ($parsed->port !== null ? (string) $parsed->port : null),
            'username' => $parameters['user'] ?? $parsed->username,
            'password' => $parameters['password'] ?? $parsed->password,
            'database' => $parameters['dbname'] ?? $parsed->database,
            'parameters' => array_diff_key($parameters, array_flip(['host', 'port', 'user', 'password', 'dbname'])),
        ];

        if ($arguments['host'] === null) {
            throw DatabaseConnectionStringWasInvalid::hostWasMissing();
        }

        return new self(...[
            ...array_filter($arguments, fn (mixed $value) => $value !== null),
            ...$options,
        ]);
    }
}
