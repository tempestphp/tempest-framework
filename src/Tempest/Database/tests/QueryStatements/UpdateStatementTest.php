<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\EmptyUpdateStatement;
use Tempest\Database\Exceptions\InvalidUpdateStatement;
use Tempest\Database\QueryStatements\UpdateStatement;
use Tempest\Database\QueryStatements\WhereStatement;

use function Tempest\Support\arr;

#[CoversClass(UpdateStatement::class)]
final class UpdateStatementTest extends TestCase
{
    public function test_update(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new UpdateStatement(
            table: $tableDefinition,
            values: arr(['bar' => 2, 'baz' => 3]),
            where: arr([new WhereStatement('`bar` = ?')]),
        );

        $expected = <<<SQL
        UPDATE `foo`
        SET `bar` = ?, `baz` = ?
        WHERE `bar` = ?
        SQL;

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::SQLITE));
        $this->assertSame($expected, $statement->compile(DatabaseDialect::POSTGRESQL));
    }

    public function test_exception_when_no_values(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $this->expectException(EmptyUpdateStatement::class);

        new UpdateStatement(
            table: $tableDefinition,
            allowAll: true,
        )->compile(DatabaseDialect::MYSQL);
    }

    public function test_exception_when_no_conditions_without_explicit_allow_all(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $this->expectException(InvalidUpdateStatement::class);

        new UpdateStatement(
            table: $tableDefinition,
        )->compile(DatabaseDialect::MYSQL);
    }

    public function test_no_exception_when_no_conditions_with_explicit_allow_all(): void
    {
        $tableDefinition = new TableDefinition('foo', 'bar');

        $statement = new UpdateStatement(
            table: $tableDefinition,
            values: arr(['bar' => 2, 'baz' => 3]),
            allowAll: true,
        );

        $expected = <<<SQL
        UPDATE `foo`
        SET `bar` = ?, `baz` = ?
        SQL;

        $this->assertSame($expected, $statement->compile(DatabaseDialect::MYSQL));
    }
}
