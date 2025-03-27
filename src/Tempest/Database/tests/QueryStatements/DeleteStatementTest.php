<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\InvalidDeleteStatement;
use Tempest\Database\QueryStatements\DeleteStatement;
use Tempest\Database\QueryStatements\WhereStatement;
use function Tempest\Support\arr;

final class DeleteStatementTest extends TestCase
{
    public function test_delete(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new DeleteStatement(
            table: $tableDefinition,
            where: arr([new WhereStatement('`bar` = "1"')])
        );

        $expected = <<<SQL
        DELETE FROM `foo`
        WHERE `bar` = "1"
        SQL;

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::SQLITE));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::POSTGRESQL));
    }

    public function test_exception_when_no_condition_is_set(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $this->expectException(InvalidDeleteStatement::class);

        new DeleteStatement(table: $tableDefinition)
            ->compile(DatabaseDialect::MYSQL);
    }

    public function test_no_exception_when_allow_all(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $compiled = new DeleteStatement(table: $tableDefinition, allowAll: true)
            ->compile(DatabaseDialect::MYSQL);

        $this->assertSame('DELETE FROM `foo`', $compiled);
    }
}
