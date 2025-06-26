<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\InsertColumnsMismatched;
use Tempest\Database\QueryStatements\InsertStatement;

use function Tempest\Support\arr;

final class InsertStatementTest extends TestCase
{
    public function test_insert_statement(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new InsertStatement($tableDefinition, arr([
            ['foo' => 1, 'bar' => 2],
            arr(['foo' => 3, 'bar' => 4]),
        ]));

        $expected = <<<SQL
        INSERT INTO `foo` AS `bar` (`foo`, `bar`)
        VALUES (?, ?), (?, ?)
        SQL;

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::SQLITE));

        $expectedPostgres = <<<PSQL
        INSERT INTO `foo` AS `bar` (`foo`, `bar`)
        VALUES (?, ?), (?, ?) RETURNING *
        PSQL;

        $this->assertSame($expectedPostgres, $statement->compile(DatabaseDialect::POSTGRESQL));
    }

    public function test_exception_on_column_mismatch(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new InsertStatement($tableDefinition, arr([
            ['foo' => 1, 'bar' => 2, 'boo' => 3],
            arr(['baz' => 3, 'bar' => 4]),
        ]));

        $this->expectException(InsertColumnsMismatched::class);
        $this->expectExceptionMessage('Expected columns `foo`, `bar` and `boo`; but got `baz` and `bar`');

        $statement->compile(DatabaseDialect::MYSQL);
    }
}
