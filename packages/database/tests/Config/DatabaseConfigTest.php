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
use Tempest\Database\Exceptions\DatabaseUrlWasInvalid;

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

    #[DataProvider('provide_database_urls')]
    #[Test]
    public function driver_can_be_created_from_url(DatabaseConfig $driver, string $dsn, ?string $username, ?string $password): void
    {
        $this->assertSame($dsn, $driver->dsn);
        $this->assertSame($username, $driver->username);
        $this->assertSame($password, $driver->password);
    }

    public static function provide_database_urls(): Generator
    {
        yield 'mysql' => [
            MysqlConfig::fromUrl('mysql://user:secret@db.example.com:3307/tempest'),
            'mysql:host=db.example.com:3307;dbname=tempest',
            'user',
            'secret',
        ];

        yield 'mariadb' => [
            MysqlConfig::fromUrl('mariadb://user:secret@db.example.com:3307/tempest'),
            'mysql:host=db.example.com:3307;dbname=tempest',
            'user',
            'secret',
        ];

        yield 'mysql with encoded credentials' => [
            MysqlConfig::fromUrl('mysql://us%3Aer:p%40ss%2Fw%3Ard@db.example.com/tempest'),
            'mysql:host=db.example.com:3306;dbname=tempest',
            'us:er',
            'p@ss/w:rd',
        ];

        yield 'mysql with defaults' => [
            MysqlConfig::fromUrl('mysql://db.example.com'),
            'mysql:host=db.example.com:3306;dbname=app',
            'root',
            '',
        ];

        yield 'mysql with overrides' => [
            MysqlConfig::fromUrl('mysql://user:secret@db.example.com:3307/tempest', database: 'other', username: 'admin'),
            'mysql:host=db.example.com:3307;dbname=other',
            'admin',
            'secret',
        ];

        yield 'postgres' => [
            PostgresConfig::fromUrl('postgres://user:secret@db.example.com:5433/tempest'),
            'pgsql:host=db.example.com;port=5433;dbname=tempest;user=user;password=secret',
            'user',
            'secret',
        ];

        yield 'postgresql' => [
            PostgresConfig::fromUrl('postgresql://user:secret@db.example.com/tempest'),
            'pgsql:host=db.example.com;port=5432;dbname=tempest;user=user;password=secret',
            'user',
            'secret',
        ];

        yield 'pgsql' => [
            PostgresConfig::fromUrl('pgsql://db.example.com'),
            'pgsql:host=db.example.com;port=5432;dbname=app;user=postgres;password=',
            'postgres',
            '',
        ];

        yield 'sqlite with absolute path' => [
            SQLiteConfig::fromUrl('sqlite:///usr/local/db.sqlite'),
            'sqlite:/usr/local/db.sqlite',
            null,
            null,
        ];

        yield 'sqlite in memory' => [
            SQLiteConfig::fromUrl('sqlite::memory:'),
            'sqlite::memory:',
            null,
            null,
        ];
    }

    #[Test]
    public function url_options_are_passed_to_the_driver(): void
    {
        $config = MysqlConfig::fromUrl(
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

    #[DataProvider('provide_invalid_database_urls')]
    #[Test]
    public function invalid_url_throws(callable $factory): void
    {
        $this->expectException(DatabaseUrlWasInvalid::class);

        $factory();
    }

    public static function provide_invalid_database_urls(): Generator
    {
        yield 'mysql with postgres scheme' => [fn () => MysqlConfig::fromUrl('postgres://user:secret@db.example.com/tempest')];
        yield 'postgres with mysql scheme' => [fn () => PostgresConfig::fromUrl('mysql://user:secret@db.example.com/tempest')];
        yield 'mysql without host' => [fn () => MysqlConfig::fromUrl('mysql:///tempest')];
        yield 'mysql without scheme' => [fn () => MysqlConfig::fromUrl('db.example.com')];
        yield 'empty url' => [fn () => MysqlConfig::fromUrl('')];
        yield 'sqlite with mysql scheme' => [fn () => SQLiteConfig::fromUrl('mysql://db.example.com/tempest')];
        yield 'sqlite without path' => [fn () => SQLiteConfig::fromUrl('sqlite://')];
    }
}
