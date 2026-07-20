<?php

declare(strict_types=1);

namespace Tempest\Database\Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Connection\PDOConnection;
use Tempest\Database\Exceptions\ConnectionClosed;

/**
 * @internal
 */
final class PDOConnectionTest extends TestCase
{
    private const string PATH = ':memory:';

    #[DataProvider('provideQueryMethods')]
    #[Test]
    public function connection_must_be_open(string $method, array $params): void
    {
        $this->expectException(ConnectionClosed::class);

        $connection = new PDOConnection(new SQLiteConfig(self::PATH));

        $connection->$method(...$params);
    }

    #[DataProvider('provideQueryMethods')]
    #[Test]
    public function close_must_be_open(string $method, array $params): void
    {
        $this->expectException(ConnectionClosed::class);

        $connection = new PDOConnection(new SQLiteConfig(self::PATH));
        $connection->connect();
        $connection->close();

        $connection->$method(...$params);
    }

    public static function provideQueryMethods(): Generator
    {
        yield 'lastInsertId' => ['lastInsertId', []];
        yield 'commit' => ['commit', []];
        yield 'rollback' => ['rollback', []];
        yield 'beginTransaction' => ['beginTransaction', []];
        yield 'prepare' => ['prepare', ['select 1']];
    }

    #[Test]
    public function commit(): void
    {
        $connection = new PDOConnection(new SQLiteConfig(self::PATH));
        $connection->connect();

        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->commit());
    }

    #[Test]
    public function rollback(): void
    {
        $connection = new PDOConnection(new SQLiteConfig(self::PATH));
        $connection->connect();

        $this->assertTrue($connection->beginTransaction());
        $this->assertTrue($connection->rollback());
    }

    #[Test]
    public function last_insert_id(): void
    {
        $connection = new PDOConnection(new SQLiteConfig(self::PATH));
        $connection->connect();

        $this->assertSame('0', $connection->lastInsertId());
    }

    #[Test]
    public function prepare(): void
    {
        $connection = new PDOConnection(new SQLiteConfig(self::PATH));
        $connection->connect();

        $this->assertNotFalse($connection->prepare('select 1'));
    }
}
