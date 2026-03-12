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
                certificateAuthority: '/etc/ssl/certs/ca-certificates.crt',
                persistent: true,
            ),
            [
                PDO::ATTR_PERSISTENT => true,
                Mysql::ATTR_SSL_CA => '/etc/ssl/certs/ca-certificates.crt',
            ],
        ];

        yield 'mysql with all SSL options' => [
            new MysqlConfig(
                certificateAuthority: '/etc/ssl/certs/ca-certificates.crt',
                verifyServerCertificate: false,
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
}
