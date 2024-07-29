<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Database;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\DatabaseDriver;
use Tempest\Database\Drivers\MySqlDriver;
use Tempest\Database\Drivers\SQLiteDriver;

/**
 * @internal
 * @small
 */
final class DatabaseDriverTest extends TestCase
{
    #[Test]
    #[DataProvider('provide_database_drivers')]
    public function driver_has_the_correct_dsn(DatabaseDriver $driver, string $dsn): void
    {
        $this->assertSame($dsn, $driver->getDsn());
    }

    public static function provide_database_drivers(): Generator
    {
        yield 'sqlite' => [
            new SQLiteDriver(path: 'localhost'),
            'sqlite:localhost',
        ];

        yield 'mysql' => [
            new MySqlDriver(
                host: 'localhost',
                port: '3307',
                username: 'user',
                password: 'secret',
                database: 'tempest'
            ),
            'mysql:host=localhost:3307;dbname=tempest',
        ];
    }
}
