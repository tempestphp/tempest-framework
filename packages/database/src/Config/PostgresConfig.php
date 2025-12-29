<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use PDO;
use SensitiveParameter;
use Tempest\Database\Tables\NamingStrategy;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;
use UnitEnum;

final class PostgresConfig implements DatabaseConfig
{
    public string $dsn {
        get => sprintf(
            'pgsql:host=%s;port=%s;dbname=%s;user=%s;password=%s',
            $this->host,
            $this->port,
            $this->database,
            $this->username,
            $this->password,
        );
    }

    public DatabaseDialect $dialect {
        get => DatabaseDialect::POSTGRESQL;
    }

    public bool $usePersistentConnection {
        get => $this->persistent;
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
     * @param string $host The PostgreSQL server hostname or IP address.
     * @param string $port The PostgreSQL server port number.
     * @param string $username The PostgreSQL username for authentication.
     * @param string $password The PostgreSQL password for authentication.
     * @param string $database The database name to connect to.
     * @param bool $persistent Whether to use persistent connections. Persistent connections are not closed at the end of the script and are cached for reuse when another script requests a connection using the same credentials.
     * @param NamingStrategy $namingStrategy The naming strategy for database tables and columns.
     * @param string|UnitEnum|null $tag An optional tag to identify this database configuration.
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
        public null|string|UnitEnum $tag = null,
    ) {}
}
