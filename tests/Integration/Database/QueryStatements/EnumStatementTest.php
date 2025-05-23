<?php

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\EnumStatement;
use Tests\Tempest\Integration\Database\Fixtures\EnumForCreateTable;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class EnumStatementTest extends FrameworkIntegrationTestCase
{
    public function test_it_can_compile_an_enum_statement_for_mysql(): void
    {
        $enumStatement = new EnumStatement(
            name: 'enum',
            enumClass: EnumForCreateTable::class,
        );

        $this->assertSame(
            "`enum` ENUM('foo', 'bar')  NOT NULL",
            $enumStatement->compile(DatabaseDialect::MYSQL),
        );
    }

    public function test_it_can_compile_an_enum_statement_for_sqlite(): void
    {
        $enumStatement = new EnumStatement(
            name: 'enum',
            enumClass: EnumForCreateTable::class,
        );

        $this->assertSame(
            '`enum` TEXT  NOT NULL',
            $enumStatement->compile(DatabaseDialect::SQLITE),
        );
    }

    public function test_it_can_compile_an_enum_statement_for_postgresql(): void
    {
        $enumStatement = new EnumStatement(
            name: 'enum',
            enumClass: EnumForCreateTable::class,
        );

        $this->assertSame(
            '"enum" "Tests\Tempest\Integration\Database\Fixtures\EnumForCreateTable"  NOT NULL',
            $enumStatement->compile(DatabaseDialect::POSTGRESQL),
        );
    }
}
