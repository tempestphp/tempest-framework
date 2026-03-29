<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\TestCase;
use Tempest\Database\Builder\FieldDefinition;
use Tempest\Database\Builder\TableDefinition;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\CharStatement;
use Tempest\Database\QueryStatements\GroupByStatement;
use Tempest\Database\QueryStatements\HavingStatement;
use Tempest\Database\QueryStatements\JoinStatement;
use Tempest\Database\QueryStatements\OrderByStatement;
use Tempest\Database\QueryStatements\SelectStatement;
use Tempest\Database\QueryStatements\WhereStatement;

use function Tempest\Support\arr;

final class CharStatementTest extends TestCase
{
    public function test_char(): void
    {
        $statement = new CharStatement(
            name: 'foo',
            size: 36,
            nullable: false,
            default: '019d38a9-5504-7a16-ab9d-520bbc289ecc',
        );

        $expectedMysql = '`foo` CHAR(36) DEFAULT \'019d38a9-5504-7a16-ab9d-520bbc289ecc\' NOT NULL';

        $this->assertSame($expectedMysql, $statement->compile(DatabaseDialect::MYSQL));
    }
}
