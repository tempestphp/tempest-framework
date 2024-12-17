<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use RuntimeException;
use Tempest\Database\DatabaseDialect;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\Exceptions\InvalidDefaultValue;
use Tempest\Database\Exceptions\InvalidValue;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\UnsupportedDialect;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CreateTableStatementTest extends FrameworkIntegrationTestCase
{
    public function test_defaults(): void
    {
        $migration = new class () implements DatabaseMigration {
            private(set) string $name = '0000_test_migration';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('table')
                    ->text('text', default: 'default')
                    ->char('char', default: 'd')
                    ->varchar('varchar', default: 'default')
                    ->float('float', default: 0.1)
                    ->integer('integer', default: 1)
                    ->date('date', default: '2024-01-01')
                    ->datetime('datetime', default: '2024-01-01 00:00:00')
                    ->boolean('is_active', default: true)
                    ->json('json', default: '{"default": "foo"}')
                    ->index('integer')
                    ->unique('date', 'datetime');
            }

            public function down(): QueryStatement|null
            {
                return null;
            }
        };

        $this->migrate(
            CreateMigrationsTable::class,
            $migration,
        );

        $this->expectNotToPerformAssertions();
    }

    public function test_set_statement(): void
    {
        $migration = new class () implements DatabaseMigration {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return (new CreateTableStatement('table'))
                    ->set('set', values: ['foo', 'bar'], default: 'foo');
            }

            public function down(): QueryStatement|null
            {
                return null;
            }
        };

        match ($this->container->get(DatabaseDialect::class)) {
            DatabaseDialect::MYSQL => '',
            DatabaseDialect::SQLITE => $this->expectException(UnsupportedDialect::class),
            DatabaseDialect::POSTGRESQL => $this->expectException(UnsupportedDialect::class),
            null => throw new RuntimeException('No database dialect available'),
        };

        $this->migrate(
            CreateMigrationsTable::class,
            $migration,
        );

        $this->expectNotToPerformAssertions();
    }

    public function test_invalid_json_default(): void
    {
        $migration = new class () implements DatabaseMigration {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return (new CreateTableStatement('table'))
                    ->json('json', default: '{default: "invalid json"}');
            }

            public function down(): QueryStatement|null
            {
                return null;
            }
        };

        $this->expectException(InvalidDefaultValue::class);
        $this->expectExceptionMessage("Default value '{default: \"invalid json\"}' provided for json is not valid");

        $this->migrate(
            CreateMigrationsTable::class,
            $migration,
        );
    }

    public function test_invalid_set_values(): void
    {
        $migration = new class () implements DatabaseMigration {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return (new CreateTableStatement('table'))
                    ->set('set', values: []);
            }

            public function down(): QueryStatement|null
            {
                return null;
            }
        };

        $this->expectException(InvalidValue::class);
        $this->expectExceptionMessage("Value '[]' provided for set is not valid");

        $this->migrate(
            CreateMigrationsTable::class,
            $migration,
        );
    }
}
