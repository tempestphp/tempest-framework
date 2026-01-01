<?php

declare(strict_types=1);

namespace Tempest\Database\Config;

use PDO;
use Pdo\Mysql;
use SensitiveParameter;
use Tempest\Database\Tables\NamingStrategy;
use Tempest\Database\Tables\PluralizedSnakeCaseStrategy;
use UnitEnum;

final class MysqlConfig implements DatabaseConfig
{
    public string $dsn {
        get => sprintf(
            'mysql:host=%s:%s;dbname=%s',
            $this->host,
            $this->port,
            $this->database,
        );
    }

    public DatabaseDialect $dialect {
        get => DatabaseDialect::MYSQL;
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
     * @param string|UnitEnum|null $tag An optional tag to identify this database configuration.
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
        public null|string|UnitEnum $tag = null,
    ) {}
}
