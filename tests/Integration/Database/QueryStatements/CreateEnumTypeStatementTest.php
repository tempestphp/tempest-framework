<?php

namespace Tests\Tempest\Integration\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\CreateEnumTypeStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class CreateEnumTypeStatementTest extends FrameworkIntegrationTestCase
{
    public function test_it_can_compile_create_enum_type_statement(): void
    {
        $enumStatement = new CreateEnumTypeStatement(
            enumClass: CreateEnumTypeStatementTestEnumForCreateTable::class,
        );

        $this->assertSame(
            <<<PSQL
            CREATE TYPE "Tests\Tempest\Integration\Database\QueryStatements\CreateEnumTypeStatementTestEnumForCreateTable" AS ENUM ('foo', 'bar');
            PSQL,
            $enumStatement->compile(DatabaseDialect::POSTGRESQL),
        );
    }
}

enum CreateEnumTypeStatementTestEnumForCreateTable: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}
