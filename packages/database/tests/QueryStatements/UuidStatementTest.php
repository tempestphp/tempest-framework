<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\UuidStatement;

/**
 * @internal
 */
final class UuidStatementTest extends TestCase
{
    #[Test]
    public function mysql_compilation(): void
    {
        $statement = new UuidStatement('author_id');
        $compiled = $statement->compile(DatabaseDialect::MYSQL);

        $this->assertSame('`author_id` CHAR(36) NOT NULL', $compiled);
    }

    #[Test]
    public function postgresql_compilation(): void
    {
        $statement = new UuidStatement('author_id');
        $compiled = $statement->compile(DatabaseDialect::POSTGRESQL);

        $this->assertSame('"author_id" UUID NOT NULL', $compiled);
    }

    #[Test]
    public function sqlite_compilation(): void
    {
        $statement = new UuidStatement('author_id');
        $compiled = $statement->compile(DatabaseDialect::SQLITE);

        $this->assertSame('`author_id` TEXT NOT NULL', $compiled);
    }

    #[Test]
    public function nullable_compilation(): void
    {
        $statement = new UuidStatement('author_id', nullable: true);

        $this->assertSame('`author_id` CHAR(36)', $statement->compile(DatabaseDialect::MYSQL));
        $this->assertSame('"author_id" UUID', $statement->compile(DatabaseDialect::POSTGRESQL));
        $this->assertSame('`author_id` TEXT', $statement->compile(DatabaseDialect::SQLITE));
    }
}
