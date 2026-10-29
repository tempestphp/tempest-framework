<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use PDO;
use Pdo\Mysql;
use SensitiveParameter;
use Tempest\Database\Exceptions\DatabaseConnectionStringWasInvalid;
use Tempest\Database\Migrations\DatePrefixStrategy;
use Tempest\Database\Migrations\MigrationNamingStrategy;
use Tempest\Database\Tables\NamingStrategy;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;
use UnitEnum;

final class MysqlConfig implements DatabaseConfig
{
    public string $dsn {
        get => sprintf(
            'mysql:host=%s:%s;dbname=%s%s',
            $this->host,
            $this->port,
            $this->database,
            implode('', array_map(
                fn (string $key, string $value) => ";{$key}={$value}",
                array_keys($this->parameters),
                $this->parameters,
            )),
        );
    }

    public DatabaseDialect $dialect {
        get => DatabaseDialect::MYSQL;
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

            if ($this->certificateAuthority !== null) {
                $options[Mysql::ATTR_SSL_CA] = $this->certificateAuthority;
            }

            if ($this->verifyServerCertificate !== null) {
                $options[Mysql::ATTR_SSL_VERIFY_SERVER_CERT] = $this->verifyServerCertificate;
            }

            if ($this->clientCertificate !== null) {
                $options[Mysql::ATTR_SSL_CERT] = $this->clientCertificate;
            }

            if ($this->clientKey !== null) {
                $options[Mysql::ATTR_SSL_KEY] = $this->clientKey;
            }

            return $options;
        }
    }

    /**
     * @param string $host The MySQL server hostname or IP address.
     * @param string $port The MySQL server port number.
     * @param string $username The MySQL username for authentication.
     * @param string $password The MySQL password for authentication.
     * @param string $database The database name to connect to.
     * @param bool $persistent Whether to use persistent connections. Persistent connections are not closed at the end of the script and are cached for reuse when another script requests a connection using the same credentials.
     * @param bool|null $verifyServerCertificate Whether to verify the server's SSL certificate. Set to false for self-signed certificates (not recommended for production).
     * @param string|null $certificateAuthority Path to the SSL Certificate Authority (CA) file. Required for SSL/TLS connections to verify the server's certificate.
     * @param string|null $clientCertificate Path to the client's SSL certificate file. Used for mutual TLS authentication.
     * @param string|null $clientKey Path to the client's SSL private key file. Used for mutual TLS authentication.
     * @param NamingStrategy $namingStrategy The naming strategy for database tables and columns.
     * @param MigrationNamingStrategy $migrationNaming The naming strategy for migration file prefixes.
     * @param string|UnitEnum|null $tag An optional tag to identify this database configuration.
     * @param array<string,string> $parameters Additional parameters appended to the DSN, such as `unix_socket` or `charset`.
     */
    public function __construct(
        #[SensitiveParameter]
        public string $host = 'localhost',
        #[SensitiveParameter]
        public string $port = '3306',
        #[SensitiveParameter]
        public string $username = 'root',
        #[SensitiveParameter]
        public string $password = '',
        #[SensitiveParameter]
        public string $database = 'app',
        public bool $persistent = false,
        public ?bool $verifyServerCertificate = null,
        public ?string $certificateAuthority = null,
        public ?string $clientCertificate = null,
        public ?string $clientKey = null,
        public NamingStrategy $namingStrategy = new PluralizedSnakeCaseStrategy(),
        public MigrationNamingStrategy $migrationNaming = new DatePrefixStrategy(),
        public string|UnitEnum|null $tag = null,
        public array $parameters = [],
    ) {}

    /**
     * Creates a MySQL configuration from a connection string, such as `mysql://user:password@host:3306/database?charset=utf8mb4`. Components missing from the connection string fall back to their defaults, and query parameters are appended to the DSN. A Unix socket may be given as a percent-encoded host or as the `unix_socket` query parameter.
     *
     * @param string $connectionString The connection string. Supported schemes are `mysql` and `mariadb`.
     * @param mixed ...$options Named constructor arguments, which take precedence over the values parsed from the connection string.
     */
    public static function fromConnectionString(#[SensitiveParameter] string $connectionString, mixed ...$options): self
    {
        $parsed = DatabaseConnectionString::from($connectionString);

        if (! in_array($parsed->scheme, ['mysql', 'mariadb'], strict: true)) {
            throw DatabaseConnectionStringWasInvalid::schemeWasNotSupported($parsed->scheme, ['mysql', 'mariadb']);
        }

        $host = $parsed->host;
        $parameters = $parsed->parameters;

        if ($host !== null && str_starts_with($host, '/')) {
            $parameters['unix_socket'] ??= $host;
            $host = null;
        }

        if ($host === null && ! isset($parameters['unix_socket'])) {
            throw DatabaseConnectionStringWasInvalid::hostWasMissing();
        }

        $arguments = array_filter(
            [
                'host' => $host,
                'port' => $parsed->port !== null ? (string) $parsed->port : null,
                'username' => $parsed->username,
                'password' => $parsed->password,
                'database' => $parsed->database,
                'parameters' => $parameters,
            ],
            fn (mixed $value) => $value !== null,
        );

        return new self(...[...$arguments, ...$options]);
    }
}
