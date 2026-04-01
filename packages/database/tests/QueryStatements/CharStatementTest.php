<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\CharStatement;

final class CharStatementTest extends TestCase
{
    #[Test]
    public function test_char(): void
    {
        $statement = new CharStatement(
            name: 'foo',
            size: 36,
            nullable: false,
            default: '019d38a9-5504-7a16-ab9d-520bbc289ecc',
        );

        $expectedMysql = '`foo` CHAR(36) DEFAULT \'019d38a9-5504-7a16-ab9d-520bbc289ecc\' NOT NULL';
        $expectedPgsql = '"foo" CHAR(36) DEFAULT \'019d38a9-5504-7a16-ab9d-520bbc289ecc\' NOT NULL';

        $this->assertSame($expectedMysql, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expectedPgsql, $statement->compile(DatabaseDialect::POSTGRESQL));
    }
}
