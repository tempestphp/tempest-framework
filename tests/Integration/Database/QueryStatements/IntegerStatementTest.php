<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
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
    public function unsigned_columns_are_created_on_whichever_database_is_configured(): void
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
    public function postgres_is_not_told_about_a_keyword_it_does_not_have(): void
    {
        $statement = new IntegerStatement('votes', unsigned: true)->compile(DatabaseDialect::POSTGRESQL);

        $this->assertStringNotContainsString('UNSIGNED', $statement);
        $this->assertStringContainsString('INTEGER', $statement);
    }

    #[Test]
    public function mysql_keeps_its_unsigned_range(): void
    {
        // MySQL supports `UNSIGNED`; dropping it globally would silently halve the requested range.
        $this->assertStringContainsString(
            'UNSIGNED',
            new IntegerStatement('votes', unsigned: true)->compile(DatabaseDialect::MYSQL),
        );

        $this->assertStringNotContainsString(
            'UNSIGNED',
            new IntegerStatement('votes')->compile(DatabaseDialect::MYSQL),
        );
    }

    #[Test]
    public function the_size_chooses_the_type(): void
    {
        foreach ([DatabaseDialect::MYSQL, DatabaseDialect::POSTGRESQL] as $dialect) {
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
}
