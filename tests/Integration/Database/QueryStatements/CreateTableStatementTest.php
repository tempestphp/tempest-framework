<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\Config\DatabaseConfig;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\DialectWasNotSupported;
use Tempest\Database\Exceptions\DefaultValueWasInvalid;
use Tempest\Database\Exceptions\ValueWasInvalid;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\CreateEnumTypeStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropEnumTypeStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CreateTableStatementTest extends FrameworkIntegrationTestCase
{
    public function test_defaults(): void
    {
        $migration = new class() implements MigratesUp {
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
        };

        $this->database->migrate(
            CreateMigrationsTable::class,
            $migration,
        );

        $this->expectNotToPerformAssertions();
    }

    public function test_set_statement(): void
    {
        $migration = new class() implements MigratesUp {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->set('set', values: ['foo', 'bar'], default: 'foo');
            }
        };

        $dialect = $this->container->get(DatabaseConfig::class)->dialect;

        match ($dialect) {
            DatabaseDialect::MYSQL => $this->expectNotToPerformAssertions(),
            DatabaseDialect::SQLITE => $this->expectException(DialectWasNotSupported::class),
            DatabaseDialect::POSTGRESQL => $this->expectException(DialectWasNotSupported::class),
        };

        $this->database->migrate(
            CreateMigrationsTable::class,
            $migration,
        );
    }

    public function test_array_statement(): void
    {
        $migration = new class() implements MigratesUp {
            public string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->array('test_array', default: ['foo', 'bar']);
            }
        };

        $this->database->migrate(
            CreateMigrationsTable::class,
            $migration,
        );

        $this->expectNotToPerformAssertions();
    }

    public function test_enum_statement(): void
    {
        $this->database->migrate(CreateMigrationsTable::class);

        if ($this->container->get(Database::class)->dialect === DatabaseDialect::POSTGRESQL) {
            $enumTypeMigration = new class() implements MigratesUp {
                public string $name = '0';

                public function up(): QueryStatement
                {
                    return new CompoundStatement(
                        new DropEnumTypeStatement(CreateTableStatementTestEnumForCreateTable::class),
                        new CreateEnumTypeStatement(CreateTableStatementTestEnumForCreateTable::class),
                    );
                }
            };

            $this->database->migrate($enumTypeMigration);
        }

        $tableMigration = new class() implements MigratesUp {
            public string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->enum(
                        name: 'enum',
                        enumClass: CreateTableStatementTestEnumForCreateTable::class,
                        default: CreateTableStatementTestEnumForCreateTable::BAR,
                    );
            }
        };

        $this->database->migrate($tableMigration);

        $this->expectNotToPerformAssertions();
    }

    public function test_invalid_json_default(): void
    {
        $migration = new class() implements MigratesUp {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->json('json', default: '{default: "invalid json"}');
            }
        };

        $this->expectException(DefaultValueWasInvalid::class);
        $this->expectExceptionMessage("Default value '{default: \"invalid json\"}' provided for json is not valid");

        $this->database->migrate(
            CreateMigrationsTable::class,
            $migration,
        );
    }

    public function test_dto_field(): void
    {
        $migration = new class() implements MigratesUp {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->dto('dto');
            }
        };

        $this->database->migrate(
            CreateMigrationsTable::class,
            $migration,
        );

        $this->expectNotToPerformAssertions();
    }

    public function test_invalid_set_values(): void
    {
        $migration = new class() implements MigratesUp {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->set('set', values: []);
            }
        };

        $this->expectException(ValueWasInvalid::class);
        $this->expectExceptionMessage("Value '[]' provided for set is not valid");

        $this->database->migrate(
            CreateMigrationsTable::class,
            $migration,
        );
    }

    public function test_string_method_integration(): void
    {
        $migration = new class() implements MigratesUp {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('frieren_table')
                    ->primary()
                    ->string('name', length: 100, nullable: false, default: 'Frieren')
                    ->string('type', nullable: true);
            }
        };

        $this->database->migrate(CreateMigrationsTable::class, $migration);

        $this->expectNotToPerformAssertions();
    }

    public function test_string_method_with_custom_parameters(): void
    {
        $varcharStatement = new CreateTableStatement('frieren_mages')
            ->primary()
            ->varchar('name', length: 120, nullable: true, default: 'Himmel')
            ->compile(DatabaseDialect::MYSQL);

        $stringStatement = new CreateTableStatement('frieren_mages')
            ->primary()
            ->varchar('name', length: 120, nullable: true, default: 'Himmel')
            ->compile(DatabaseDialect::MYSQL);

        $this->assertSame($varcharStatement, $stringStatement);
    }

    public function test_object_field(): void
    {
        $migration = new class() implements MigratesUp {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->object('object_data');
            }
        };

        $this->database->migrate(CreateMigrationsTable::class, $migration);

        $this->expectNotToPerformAssertions();
    }

    public function test_object_field_with_default(): void
    {
        $migration = new class() implements MigratesUp {
            private(set) string $name = '0';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('test_table')
                    ->object('object_data', default: '{"name": "Frieren", "age": 1000}');
            }
        };

        $this->database->migrate(CreateMigrationsTable::class, $migration);

        $this->expectNotToPerformAssertions();
    }

    public function test_object_method_produces_same_sql_as_json_and_dto(): void
    {
        $jsonStatement = new CreateTableStatement('test_table')
            ->json('data')
            ->compile(DatabaseDialect::MYSQL);

        $dtoStatement = new CreateTableStatement('test_table')
            ->dto('data')
            ->compile(DatabaseDialect::MYSQL);

        $objectStatement = new CreateTableStatement('test_table')
            ->object('data')
            ->compile(DatabaseDialect::MYSQL);

        $this->assertSame($jsonStatement, $dtoStatement);
        $this->assertSame($jsonStatement, $objectStatement);
        $this->assertSame($dtoStatement, $objectStatement);
    }
}

enum CreateTableStatementTestEnumForCreateTable: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
