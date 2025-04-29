<?php

namespace Tempest\Database\Tests\Unit\QueryStatements;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\CountStatement;

final class CountStatementTest extends TestCase
{
    public function test_count_statement(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new CountStatement(
            table: $tableDefinition,
            column: null,
        );

        $expected = <<<SQL
        SELECT COUNT(*)
        FROM `foo`
        SQL;

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::POSTGRESQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::SQLITE));
    }

    public function test_count_statement_with_specified_column(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new CountStatement(
            table: $tableDefinition,
            column: 'foobar',
        );

        $expected = <<<SQL
        SELECT COUNT(`foobar`)
        FROM `foo`
        SQL;

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::POSTGRESQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::SQLITE));
    }

    public function test_count_statement_with_distinct_specified_column(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new CountStatement(
            table: $tableDefinition,
            column: 'foobar',
        );

        $statement->distinct = true;

        $expected = <<<SQL
        SELECT COUNT(DISTINCT `foobar`)
        FROM `foo`
        SQL;

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::POSTGRESQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::SQLITE));
    }
}
