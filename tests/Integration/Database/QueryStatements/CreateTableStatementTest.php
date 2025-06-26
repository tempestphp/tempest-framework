<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use RuntimeException;
use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\DatabaseMigration;
use Tempest\Database\DialectWasNotSupported;
use Tempest\Database\Exceptions\DefaultValueWasInvalid;
use Tempest\Database\Exceptions\ValueWasInvalid;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\CreateEnumTypeStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropEnumTypeStatement;
use Tests\Tempest\Integration\Database\Fixtures\EnumForCreateTable;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CreateTableStatementTest extends FrameworkIntegrationTestCase
{
    public function test_defaults(): void
    {
        $migration = new class() implements DatabaseMigration {
            private(set) string $name = '0000_test_migration';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
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

            public function down(): ?QueryStatement
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
        $migration = new class() implements DatabaseMigration {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->set('set', values: ['foo', 'bar'], default: 'foo');
            }

            public function down(): ?QueryStatement
            {
                return null;
            }
        };

        $dialect = $this->container->get(DatabaseConfig::class)?->dialect;
        match ($dialect) {
            DatabaseDialect::MYSQL => $this->expectNotToPerformAssertions(),
            DatabaseDialect::SQLITE => $this->expectException(DialectWasNotSupported::class),
            DatabaseDialect::POSTGRESQL => $this->expectException(DialectWasNotSupported::class),
            null => throw new RuntimeException('No database dialect available'),
        };

        $this->migrate(
            CreateMigrationsTable::class,
            $migration,
        );
    }

    public function test_array_statement(): void
    {
        $migration = new class() implements DatabaseMigration {
            public string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->array('test_array', default: ['foo', 'bar']);
            }

            public function down(): ?QueryStatement
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

    public function test_enum_statement(): void
    {
        $this->migrate(CreateMigrationsTable::class);

        if ($this->container->get(Database::class)->dialect === DatabaseDialect::POSTGRESQL) {
            $enumTypeMigration = new class() implements DatabaseMigration {
                public string $name = '0';

                public function up(): QueryStatement
                {
                    return new CompoundStatement(
                        new DropEnumTypeStatement(EnumForCreateTable::class),
                        new CreateEnumTypeStatement(EnumForCreateTable::class),
                    );
                }

                public function down(): ?QueryStatement
                {
                    return null;
                }
            };

            $this->migrate($enumTypeMigration);
        }

        $tableMigration = new class() implements DatabaseMigration {
            public string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->enum(
                        name: 'enum',
                        enumClass: EnumForCreateTable::class,
                        default: EnumForCreateTable::BAR,
                    );
            }

            public function down(): ?QueryStatement
            {
                return null;
            }
        };

        $this->migrate($tableMigration);

        $this->expectNotToPerformAssertions();
    }

    public function test_invalid_json_default(): void
    {
        $migration = new class() implements DatabaseMigration {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->json('json', default: '{default: "invalid json"}');
            }

            public function down(): ?QueryStatement
            {
                return null;
            }
        };

        $this->expectException(DefaultValueWasInvalid::class);
        $this->expectExceptionMessage("Default value '{default: \"invalid json\"}' provided for json is not valid");

        $this->migrate(
            CreateMigrationsTable::class,
            $migration,
        );
    }

    public function test_dto_field(): void
    {
        $migration = new class() implements DatabaseMigration {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->dto('dto');
            }

            public function down(): ?QueryStatement
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

    public function test_invalid_set_values(): void
    {
        $migration = new class() implements DatabaseMigration {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->set('set', values: []);
            }

            public function down(): ?QueryStatement
            {
                return null;
            }
        };

        $this->expectException(ValueWasInvalid::class);
        $this->expectExceptionMessage("Value '[]' provided for set is not valid");

        $this->migrate(
            CreateMigrationsTable::class,
            $migration,
        );
    }
}
