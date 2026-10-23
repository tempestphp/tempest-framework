<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DatabaseIntegerSize;
use Tempest\Database\QueryStatements\IntegerStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class IntegerStatementTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function unsigned_columns_are_valid_ddl(): void
    {
        $migration = new class() implements MigratesUp {
            private(set) string $name = '0000_create_unsigned_integers_table';

            public function up(): QueryStatement
            {
                return new CreateTableStatement('unsigned_integers')
                    ->primary()
                    ->integer('small', unsigned: true, size: DatabaseIntegerSize::SMALL)
                    ->integer('regular', unsigned: true)
                    ->integer('big', unsigned: true, size: DatabaseIntegerSize::BIG)
                    ->integer('nullable_with_default', unsigned: true, nullable: true, default: 1);
            }
        };

        $this->database->migrate(CreateMigrationsTable::class, $migration);

        $this->expectNotToPerformAssertions();
    }

    #[Test]
    #[TestWith([DatabaseDialect::MYSQL, true])]
    #[TestWith([DatabaseDialect::SQLITE, true])]
    #[TestWith([DatabaseDialect::POSTGRESQL, false])]
    public function unsigned_is_only_emitted_on_dialects_that_have_it(DatabaseDialect $dialect, bool $expected): void
    {
        $statement = new IntegerStatement('votes', unsigned: true)->compile($dialect);

        if ($expected) {
            $this->assertStringContainsString('UNSIGNED', $statement);
        } else {
            $this->assertStringNotContainsString('UNSIGNED', $statement);
        }
    }

    #[Test]
    #[TestWith([DatabaseDialect::MYSQL])]
    #[TestWith([DatabaseDialect::POSTGRESQL])]
    public function the_size_chooses_the_type(DatabaseDialect $dialect): void
    {
        $this->assertStringContainsString(
            'SMALLINT',
            new IntegerStatement('n', size: DatabaseIntegerSize::SMALL)->compile($dialect),
        );

        $this->assertStringContainsString(
            'BIGINT',
            new IntegerStatement('n', size: DatabaseIntegerSize::BIG)->compile($dialect),
        );

        // A byte count is rounded up to the size that can hold it.
        $this->assertStringContainsString(
            'BIGINT',
            new IntegerStatement('n', size: 8)->compile($dialect),
        );
    }
}
