<?php

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\CreateEnumTypeStatement;
use Tests\Tempest\Integration\Database\Fixtures\EnumForCreateTable;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

class CreateEnumTypeStatementTest extends FrameworkIntegrationTestCase
{
    public function test_it_can_compile_create_enum_type_statement(): void
    {
        $enumStatement = new CreateEnumTypeStatement(
            enumClass: EnumForCreateTable::class,
        );

        $this->assertSame(
            <<<SQL
            DO $$ BEGIN
                CREATE TYPE Tests\Tempest\Integration\Database\Fixtures\EnumForCreateTable AS ('foo', 'bar', 'Tests\\\\Tempest\\\\Integration\\\\Database\\\\Fixtures\\\\EnumForCreateTable');
            EXCEPTION
                WHEN duplicate_object THEN null;
            END $$;
            SQL,
            $enumStatement->compile(DatabaseDialect::POSTGRESQL),
        );
    }
}
