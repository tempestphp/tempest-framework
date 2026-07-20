<?php

namespace Tempest\Database\Tests\QueryStatements;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\DateStatement;

final class DateStatementTest extends TestCase
{
    #[Test]
    public function date(): void
    {
        $statement = new DateStatement(
            name: 'foo',
            default: '2026-01-01',
        );

        $expectedMysql = "`foo` DATE DEFAULT '2026-01-01' NOT NULL";
        $expectedPgsql = '"foo" DATE DEFAULT \'2026-01-01\' NOT NULL';

        $this->assertSame($expectedMysql, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expectedPgsql, $statement->compile(DatabaseDialect::POSTGRESQL));
    }

    #[Test]
    public function date_with_current(): void
    {
        $statement = new DateStatement(
            name: 'foo',
            nullable: true,
            current: true,
        );

        $expectedMysql = '`foo` DATE DEFAULT (CURRENT_DATE)';
        $expectedPgsql = '"foo" DATE DEFAULT CURRENT_DATE';

        $this->assertSame($expectedMysql, $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame($expectedPgsql, $statement->compile(DatabaseDialect::POSTGRESQL));
    }

    #[Test]
    public function date_with_default_and_current(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $statement = new DateStatement(
            name: 'foo',
            nullable: true,
            default: '2026-01-01',
            current: true,
        );
        $statement->compile(DatabaseDialect::MYSQL);
    }
}
