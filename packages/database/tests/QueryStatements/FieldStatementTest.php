<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\FieldStatement;

final class FieldStatementTest extends TestCase
{
    #[Test]
    public function sqlite(): void
    {
        $this->assertSame(
            'table.field',
            new FieldStatement('table.field')->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            'table.field',
            new FieldStatement('`table`.`field`')->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            'COUNT(*) AS `count`',
            new FieldStatement('COUNT(*) AS count')->compile(DatabaseDialect::MYSQL),
        );
    }

    #[Test]
    public function mysql(): void
    {
        $this->assertSame(
            '`table`.`field`',
            new FieldStatement('`table`.`field`')->compile(DatabaseDialect::MYSQL),
        );

        $this->assertSame(
            '`table`.`field`',
            new FieldStatement('table.field')->compile(DatabaseDialect::MYSQL),
        );

        $this->assertSame(
            'COUNT(*) AS `count`',
            new FieldStatement('COUNT(*) AS count')->compile(DatabaseDialect::MYSQL),
        );
    }

    #[Test]
    public function postgres(): void
    {
        $this->assertSame(
            '"table"."field"',
            new FieldStatement('`table`.`field`')->compile(DatabaseDialect::POSTGRESQL),
        );

        $this->assertSame(
            '"table"."field"',
            new FieldStatement('table.field')->compile(DatabaseDialect::POSTGRESQL),
        );
    }

    #[Test]
    public function with_as(): void
    {
        $this->assertSame(
            'authors.name AS `authors.name`',
            new FieldStatement('authors.name AS `authors.name`')->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            'authors.name AS `authors.name`',
            new FieldStatement('authors.name AS authors.name')->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            '`authors`.`name` AS `authors.name`',
            new FieldStatement('authors.name AS `authors.name`')->compile(DatabaseDialect::MYSQL),
        );
    }

    #[Test]
    public function with_alias(): void
    {
        $this->assertSame(
            'authors.name AS `authors.name`',
            new FieldStatement('authors.name')
                ->withAlias()
                ->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            '`authors`.`name` AS `authors.name`',
            new FieldStatement('`authors`.`name`')
                ->withAlias()
                ->compile(DatabaseDialect::MYSQL),
        );
    }

    #[Test]
    public function with_alias_prefix(): void
    {
        $this->assertSame(
            'authors.name AS `parent.authors.name`',
            new FieldStatement('authors.name')
                ->withAlias()
                ->withAliasPrefix('parent')
                ->compile(DatabaseDialect::SQLITE),
        );
    }
}
