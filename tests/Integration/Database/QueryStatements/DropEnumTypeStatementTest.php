<?php

namespace Integration\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\DropEnumTypeStatement;
use Tests\Tempest\Integration\Database\Fixtures\EnumForCreateTable;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class DropEnumTypeStatementTest extends FrameworkIntegrationTestCase
{
    public function test_it_can_compile_create_enum_type_statement(): void
    {
        $enumStatement = new DropEnumTypeStatement(
            enumClass: EnumForCreateTable::class,
        );

        $this->assertSame(
            <<<PSQL
            DROP TYPE IF EXISTS "Tests\Tempest\Integration\Database\Fixtures\EnumForCreateTable";
            PSQL,
            $enumStatement->compile(DatabaseDialect::POSTGRESQL),
        );
    }
}
