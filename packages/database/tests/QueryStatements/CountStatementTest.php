<?php

namespace Tempest\Database\Tests\QueryStatements;

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

        $expected = 'SELECT COUNT(*) AS `count` FROM `foo`';

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
    }

    public function test_count_statement_with_specified_column(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new CountStatement(
            table: $tableDefinition,
            column: 'foobar',
        );

        $expected = 'SELECT COUNT(`foobar`) AS `count` FROM `foo`';

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
    }

    public function test_count_statement_with_distinct_specified_column(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new CountStatement(
            table: $tableDefinition,
            column: 'foobar',
        );

        $statement->distinct = true;

        $expected = 'SELECT COUNT(DISTINCT `foobar`) AS `count` FROM `foo`';

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
    }
}
