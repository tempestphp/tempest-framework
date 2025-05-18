<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\FieldStatement;

final class FieldStatementTest extends TestCase
{
    public function test_sqlite(): void
    {
        $this->assertSame(
            'table.field',
            new FieldStatement('table.field')->compile(DatabaseDialect::SQLITE),
        );

        $this->assertSame(
            'table.field',
            new FieldStatement('`table`.`field`')->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_mysql(): void
    {
        $this->assertSame(
            '`table`.`field`',
            new FieldStatement('`table`.`field`')->compile(DatabaseDialect::MYSQL),
        );

        $this->assertSame(
            '`table`.`field`',
            new FieldStatement('table.field')->compile(DatabaseDialect::MYSQL),
        );
    }

    public function test_postgres(): void
    {
        $this->assertSame(
            '`table`.`field`',
            new FieldStatement('`table`.`field`')->compile(DatabaseDialect::POSTGRESQL),
        );

        $this->assertSame(
            '`table`.`field`',
            new FieldStatement('table.field')->compile(DatabaseDialect::POSTGRESQL),
        );
    }

    public function test_with_as(): void
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

    public function test_with_alias(): void
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

    public function test_with_alias_prefix(): void
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
