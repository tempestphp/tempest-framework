<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\Migrations;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\RunnableMigrations;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\RawStatement;

final class RunnableMigrationsTest extends TestCase
{
    #[DataProvider('provide_migrations')]
    public function test_migration_ordering(array $migrationNames, array $expectedOrder): void
    {
        $migrations = array_map(fn (string $name) => $this->createDatabaseMigration($name), $migrationNames);

        $runnableMigrations = new RunnableMigrations($migrations);

        $this->assertSame(
            $expectedOrder,
            $this->extractMigrationNames($runnableMigrations),
        );
    }

    public static function provide_migrations(): array
    {
        return [
            [
                'migrationNames' => [
                    'migration1_1',
                    'migration1_10',
                    'migration1_2',
                    '2025-08-10_create_user_table',
                    '2025-08-01_create_book_table',
                    '2025-08-12_create_chapter_table',
                ],
                'expectedOrder' => [
                    '2025-08-01_create_book_table',
                    '2025-08-10_create_user_table',
                    '2025-08-12_create_chapter_table',
                    'migration1_1',
                    'migration1_2',
                    'migration1_10',
                ],
            ],
        ];
    }

    private function createDatabaseMigration(string $name): MigratesUp
    {
        return new class($name) implements MigratesUp {
            public function __construct(
                public string $name,
            ) {}

            public function up(): QueryStatement
            {
                return new RawStatement('SELECT 1');
            }
        };
    }

    private function extractMigrationNames(RunnableMigrations $migrations): array
    {
        return array_map(
            fn (MigratesUp $migration) => $migration->name,
            iterator_to_array($migrations),
        );
    }
}
