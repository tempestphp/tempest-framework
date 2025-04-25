<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Builder\FieldDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\GroupByStatement;
use Tempest\Database\QueryStatements\HavingStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\OrderByStatement;
use Tempest\Database\QueryStatements\SelectStatement;
use Tempest\Database\QueryStatements\WhereStatement;

use function Tempest\Support\arr;

#[CoversClass(SelectStatement::class)]
final class SelectStatementTest extends TestCase
{
    public function test_select(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new SelectStatement(
            table: $tableDefinition,
            columns: arr(['`a`', 'b', 'c', new FieldDefinition($tableDefinition, 'd', 'd_alias')]),
            join: arr(new JoinStatement('INNER JOIN foo ON bar.id = foo.id')),
            where: arr(new WhereStatement('`foo` = "bar"')),
            orderBy: arr(new OrderByStatement('`foo` DESC')),
            groupBy: arr(new GroupByStatement('`foo`')),
            having: arr(new HavingStatement('`foo` = "bar"')),
            limit: 10,
            offset: 100,
        );

        $expected = <<<SQL
        SELECT `a`, `b`, `c`, `bar`.`d` AS `d_alias`
        FROM `foo` AS `bar`
        INNER JOIN foo ON bar.id = foo.id
        WHERE `foo` = "bar"
        ORDER BY `foo` DESC
        GROUP BY `foo`
        HAVING `foo` = "bar"
        LIMIT 10
        OFFSET 100
        SQL;

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::POSTGRESQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::SQLITE));
    }
}
