<?php

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Exceptions\DefaultValueWasInvalid;
use Tempest\Database\QueryStatements\CharStatement;

final class CharStatementTest extends TestCase
{
    #[Test]
    public function char(): void
    {
        $statement = new CharStatement(
            name: 'foo',
            size: 36,
            nullable: false,
            default: '019d38a9-5504-7a16-ab9d-520bbc289ecc',
        );

        $expectedMysql = "`foo` CHAR(36) DEFAULT '019d38a9-5504-7a16-ab9d-520bbc289ecc' NOT NULL";
        $expectedPgsql = '"foo" CHAR(36) DEFAULT \'019d38a9-5504-7a16-ab9d-520bbc289ecc\' NOT NULL';

        $this->assertSame($expectedMysql, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expectedPgsql, $statement->compile(DatabaseDialect::POSTGRESQL));
    }

    #[Test]
    public function determine_char_size(): void
    {
        $fixedSizeStatement = new CharStatement(
            name: 'foo',
            size: 10,
        );
        $expectedMysql = '`foo` CHAR(10) NOT NULL';
        $this->assertSame($expectedMysql, $fixedSizeStatement->compile(DatabaseDialect::MYSQL));

        $defaultSizeStatement = new CharStatement(
            name: 'foo',
            default: 'foo_bar',
        );
        $expectedMysql = "`foo` CHAR(7) DEFAULT 'foo_bar' NOT NULL";
        $this->assertSame($expectedMysql, $defaultSizeStatement->compile(DatabaseDialect::MYSQL));

        $fixedAndDefaultSizeStatement = new CharStatement(
            name: 'foo',
            size: 7,
            default: 'foo_bar',
        );
        $expectedMysql = "`foo` CHAR(7) DEFAULT 'foo_bar' NOT NULL";
        $this->assertSame($expectedMysql, $fixedAndDefaultSizeStatement->compile(DatabaseDialect::MYSQL));
    }

    #[Test]
    public function char_size_less_than_default_value_length(): void
    {
        $this->expectException(DefaultValueWasInvalid::class);

        $statement = new CharStatement(
            name: 'foo',
            size: 1,
            default: 'foo_bar',
        );
        $statement->compile(DatabaseDialect::MYSQL);
    }

    #[Test]
    public function char_size_greater_than_default_value_length(): void
    {
        $statement = new CharStatement(
            name: 'foo',
            size: 10,
            default: 'foo_bar',
        );
        $expectedMysql = "`foo` CHAR(10) DEFAULT 'foo_bar' NOT NULL";
        $this->assertSame($expectedMysql, $statement->compile(DatabaseDialect::MYSQL));
    }
}
