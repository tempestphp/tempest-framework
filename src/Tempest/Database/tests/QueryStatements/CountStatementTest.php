<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Builder\FieldDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\CountStatement;
use Tempest\Database\QueryStatements\GroupByStatement;
use Tempest\Database\QueryStatements\HavingStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\OrderByStatement;
use Tempest\Database\QueryStatements\SelectStatement;
use Tempest\Database\QueryStatements\WhereStatement;

use function Tempest\Support\arr;

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
}
