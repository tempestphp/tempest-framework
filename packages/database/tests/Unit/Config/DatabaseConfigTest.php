<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Unit\Config;

use Generator;
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
                password: 'secret', // @mago-expect security/no-literal-password
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
                password: 'secret', // @mago-expect security/no-literal-password
                database: 'tempest',
            ),
            'pgsql:host=localhost;port=5432;dbname=tempest;user=postgres;password=secret',
            'postgres',
            'secret',
        ];
    }
}
