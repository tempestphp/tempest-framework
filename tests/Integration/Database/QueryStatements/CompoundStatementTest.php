<?php

namespace Integration\Database\QueryStatements;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\RawStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class CompoundStatementTest extends FrameworkIntegrationTestCase
{
    public function test_it_can_compile_create_enum_type_statement(): void
    {
        $statement = new CompoundStatement(
            new RawStatement('SELECT 1'),
            new RawStatement('SELECT 2'),
        );

        $this->assertSame(
            <<<SQL
            SELECT 1;
            SELECT 2;
            SQL,
            $statement->compile(DatabaseDialect::SQLITE),
        );
    }
}
