<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Config;

use Generator;
use PDO;
use Pdo\Mysql;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\Config\PostgresConfig;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Exceptions\DatabaseConnectionStringWasInvalid;

/**
 * @internal
 */
final class DatabaseConfigTest extends TestCase
{
    #[DataProvider('provide_database_drivers')]
    #[Test]
    public function driver_has_the_correct_dsn(DatabaseConfig $driver, string $dsn, ?string $username, ?string $password): void
    {
        $this->assertSame($dsn, $driver->dsn);
        $this->assertSame($username, $driver->username);
        $this->assertSame($password, $driver->password);
    }

    public static function provide_database_drivers(): Generator
    {
        yield 'sqlite' => [
            new SQLiteConfig(path: '/usr/local/db.sqlite'),
            'sqlite:/usr/local/db.sqlite',
            null,
            null,
        ];

        yield 'mysql' => [
            new MysqlConfig(
                host: 'localhost',
                port: '3307',
                username: 'user',
                password: 'secret', // @mago-expect lint:no-literal-password
                database: 'tempest',
            ),
            'mysql:host=localhost:3307;dbname=tempest',
            'user',
            'secret',
        ];

        yield 'postgresql' => [
            new PostgresConfig(
                host: 'localhost',
                port: '5432',
                username: 'postgres',
                password: 'secret', // @mago-expect lint:no-literal-password
                database: 'tempest',
            ),
            'pgsql:host=localhost;port=5432;dbname=tempest;user=postgres;password=secret',
            'postgres',
            'secret',
        ];
    }

    #[DataProvider('provide_database_drivers_with_options')]
    #[Test]
    public function driver_supports_pdo_options(DatabaseConfig $driver, array $expectedOptions): void
    {
        $this->assertSame($expectedOptions, $driver->options);
    }

    public static function provide_database_drivers_with_options(): Generator
    {
        yield 'mysql with SSL' => [
            new MysqlConfig(
                persistent: true,
                certificateAuthority: '/etc/ssl/certs/ca-certificates.crt',
            ),
            [
                PDO::ATTR_PERSISTENT => true,
                Mysql::ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
            ],
        ];

        yield 'mysql with all SSL options' => [
            new MysqlConfig(
                verifyServerCertificate: false,
                certificateAuthority: '/etc/ssl/certs/ca-certificates.crt',
                clientCertificate: '/path/to/cert.pem',
                clientKey: '/path/to/key.pem',
            ),
            [
                Mysql::ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
                Mysql::ATTR_SSL_VERIFY_SERVER_CERT => false,
                Mysql::ATTR_SSL_CERT => '/path/to/cert.pem',
                Mysql::ATTR_SSL_KEY => '/path/to/key.pem',
            ],
        ];

        yield 'postgresql with persistent' => [
            new PostgresConfig(
                persistent: true,
            ),
            [
                PDO::ATTR_PERSISTENT => true,
            ],
        ];

        yield 'sqlite with persistent' => [
            new SQLiteConfig(
                persistent: true,
            ),
            [
                PDO::ATTR_PERSISTENT => true,
            ],
        ];
    }

    #[DataProvider('provide_connection_strings')]
    #[Test]
    public function driver_can_be_created_from_connection_string(DatabaseConfig $driver, string $dsn, ?string $username, ?string $password): void
    {
        $this->assertSame($dsn, $driver->dsn);
        $this->assertSame($username, $driver->username);
        $this->assertSame($password, $driver->password);
    }

    public static function provide_connection_strings(): Generator
    {
        yield 'mysql' => [
            MysqlConfig::fromConnectionString('mysql://user:secret@db.example.com:3307/tempest'),
            'mysql:host=db.example.com:3307;dbname=tempest',
            'user',
            'secret',
        ];

        yield 'mariadb' => [
            MysqlConfig::fromConnectionString('mariadb://user:secret@db.example.com:3307/tempest'),
            'mysql:host=db.example.com:3307;dbname=tempest',
            'user',
            'secret',
        ];

        yield 'mysql with encoded credentials' => [
            MysqlConfig::fromConnectionString('mysql://us%3Aer:p%40ss%2Fw%3Ard@db.example.com/tempest'),
            'mysql:host=db.example.com:3306;dbname=tempest',
            'us:er',
            'p@ss/w:rd',
        ];

        yield 'mysql with defaults' => [
            MysqlConfig::fromConnectionString('mysql://db.example.com'),
            'mysql:host=db.example.com:3306;dbname=app',
            'root',
            '',
        ];

        yield 'mysql with overrides' => [
            MysqlConfig::fromConnectionString('mysql://user:secret@db.example.com:3307/tempest', database: 'other', username: 'admin'),
            'mysql:host=db.example.com:3307;dbname=other',
            'admin',
            'secret',
        ];

        yield 'mysql with query parameters' => [
            MysqlConfig::fromConnectionString('mysql://user:secret@db.example.com/tempest?charset=utf8mb4'),
            'mysql:host=db.example.com:3306;dbname=tempest;charset=utf8mb4',
            'user',
            'secret',
        ];

        yield 'mysql with unix socket parameter' => [
            MysqlConfig::fromConnectionString('mysql://user:secret@localhost/tempest?unix_socket=/var/run/mysqld/mysqld.sock'),
            'mysql:host=localhost:3306;dbname=tempest;unix_socket=/var/run/mysqld/mysqld.sock',
            'user',
            'secret',
        ];

        yield 'mysql with unix socket host' => [
            MysqlConfig::fromConnectionString('mysql://user:secret@%2Fvar%2Frun%2Fmysqld%2Fmysqld.sock/tempest'),
            'mysql:host=localhost:3306;dbname=tempest;unix_socket=/var/run/mysqld/mysqld.sock',
            'user',
            'secret',
        ];

        yield 'mysql with unix socket and without host' => [
            MysqlConfig::fromConnectionString('mysql://user:secret@/tempest?unix_socket=/var/run/mysqld/mysqld.sock'),
            'mysql:host=localhost:3306;dbname=tempest;unix_socket=/var/run/mysqld/mysqld.sock',
            'user',
            'secret',
        ];

        yield 'postgres' => [
            PostgresConfig::fromConnectionString('postgres://user:secret@db.example.com:5433/tempest'),
            'pgsql:host=db.example.com;port=5433;dbname=tempest;user=user;password=secret',
            'user',
            'secret',
        ];

        yield 'postgresql' => [
            PostgresConfig::fromConnectionString('postgresql://user:secret@db.example.com/tempest'),
            'pgsql:host=db.example.com;port=5432;dbname=tempest;user=user;password=secret',
            'user',
            'secret',
        ];

        yield 'pgsql' => [
            PostgresConfig::fromConnectionString('pgsql://db.example.com'),
            'pgsql:host=db.example.com;port=5432;dbname=app;user=postgres;password=',
            'postgres',
            '',
        ];

        yield 'postgres with query parameters' => [
            PostgresConfig::fromConnectionString('postgres://user:secret@db.example.com/tempest?sslmode=require&connect_timeout=10&keepalives=1'),
            'pgsql:host=db.example.com;port=5432;dbname=tempest;user=user;password=secret;sslmode=require;connect_timeout=10;keepalives=1',
            'user',
            'secret',
        ];

        yield 'postgres with unix socket parameter' => [
            PostgresConfig::fromConnectionString('postgresql://user:secret@/tempest?host=/var/run/postgresql'),
            'pgsql:host=/var/run/postgresql;port=5432;dbname=tempest;user=user;password=secret',
            'user',
            'secret',
        ];

        yield 'postgres with unix socket host' => [
            PostgresConfig::fromConnectionString('postgresql://user:secret@%2Fvar%2Frun%2Fpostgresql/tempest'),
            'pgsql:host=/var/run/postgresql;port=5432;dbname=tempest;user=user;password=secret',
            'user',
            'secret',
        ];

        yield 'postgres with connection parameters' => [
            PostgresConfig::fromConnectionString('postgresql:///?host=db.example.com&port=5433&user=user&password=secret&dbname=tempest&sslmode=verify-full'),
            'pgsql:host=db.example.com;port=5433;dbname=tempest;user=user;password=secret;sslmode=verify-full',
            'user',
            'secret',
        ];

        yield 'sqlite with absolute path' => [
            SQLiteConfig::fromConnectionString('sqlite:///usr/local/db.sqlite'),
            'sqlite:/usr/local/db.sqlite',
            null,
            null,
        ];

        yield 'sqlite in memory' => [
            SQLiteConfig::fromConnectionString('sqlite::memory:'),
            'sqlite::memory:',
            null,
            null,
        ];
    }

    #[Test]
    public function connection_string_options_are_passed_to_the_driver(): void
    {
        $config = MysqlConfig::fromConnectionString(
            'mysql://user:secret@db.example.com/tempest',
            persistent: true,
            certificateAuthority: '/etc/ssl/certs/ca-certificates.crt',
            tag: 'main',
        );

        $this->assertSame('main', $config->tag);
        $this->assertSame(
            [
                PDO::ATTR_PERSISTENT => true,
                Mysql::ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
            ],
            $config->options,
        );
    }

    #[DataProvider('provide_invalid_connection_strings')]
    #[Test]
    public function invalid_connection_string_throws(callable $factory): void
    {
        $this->expectException(DatabaseConnectionStringWasInvalid::class);

        $factory();
    }

    public static function provide_invalid_connection_strings(): Generator
    {
        yield 'mysql with postgres scheme' => [fn () => MysqlConfig::fromConnectionString('postgres://user:secret@db.example.com/tempest')];
        yield 'postgres with mysql scheme' => [fn () => PostgresConfig::fromConnectionString('mysql://user:secret@db.example.com/tempest')];
        yield 'mysql without host' => [fn () => MysqlConfig::fromConnectionString('mysql:///tempest')];
        yield 'postgres without host' => [fn () => PostgresConfig::fromConnectionString('postgres:///tempest')];
        yield 'mysql without scheme' => [fn () => MysqlConfig::fromConnectionString('db.example.com')];
        yield 'mysql with invalid port' => [fn () => MysqlConfig::fromConnectionString('mysql://db.example.com:port/tempest')];
        yield 'empty connection string' => [fn () => MysqlConfig::fromConnectionString('')];
        yield 'sqlite with mysql scheme' => [fn () => SQLiteConfig::fromConnectionString('mysql://db.example.com/tempest')];
        yield 'sqlite without path' => [fn () => SQLiteConfig::fromConnectionString('sqlite://')];
    }
}
