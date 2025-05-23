<?php

namespace Tempest\Database\Tests;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Core\Insight;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\MysqlConfig;
use Tempest\Database\Config\PostgresConfig;
use Tempest\Database\Config\SQLiteConfig;
use Tempest\Database\Database;
use Tempest\Database\DatabaseInsightsProvider;

final class DatabaseInsightsProviderTest extends TestCase
{
    #[DataProvider('provide_database_drivers')]
    public function test_get_insights(DatabaseConfig $config, string $version, array $expected): void
    {
        $database = $this->createMock(Database::class);
        $database
            ->expects($this->once())
            ->method('fetchFirst')
            ->withAnyParameters()
            ->willReturn(['version' => $version]);

        $databaseInsightsProvider = new DatabaseInsightsProvider(
            databaseConfig: $config,
            database: $database,
        );

        $this->assertEquals($expected, $databaseInsightsProvider->getInsights());
    }

    public static function provide_database_drivers(): Generator
    {
        yield 'sqlite' => [
            new SQLiteConfig(),
            '3.45.2',
            ['Engine' => 'SQLite', 'Version' => new Insight('3.45.2')],
        ];
        yield 'mysql (simple)' => [
            new MysqlConfig(),
            '8.0.42',
            ['Engine' => 'MySQL', 'Version' => new Insight('8.0.42')],
        ];
        yield 'mysql (distribution specific)' => [
            new MysqlConfig(),
            '8.0.42-0ubuntu0.22.04.1',
            ['Engine' => 'MySQL', 'Version' => new Insight('8.0.42')],
        ];
        yield 'postgresql' => [
            new PostgresConfig(),
            'PostgreSQL 15.3 on x86_64-pc-linux-gnu, compiled by gcc (GCC) 11.3.0, 64-bit',
            ['Engine' => 'PostgreSQL', 'Version' => new Insight('15.3')],
        ];
    }
}
