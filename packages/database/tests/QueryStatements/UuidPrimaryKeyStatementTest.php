<?php

declare(strict_types=1);

namespace Tempest\Database\Tests\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\QueryStatements\UuidPrimaryKeyStatement;

/**
 * @internal
 */
final class UuidPrimaryKeyStatementTest extends TestCase
{
    #[Test]
    public function mysql_compilation(): void
    {
        $statement = new UuidPrimaryKeyStatement('uuid');
        $compiled = $statement->compile(DatabaseDialect::MYSQL);

        $this->assertSame('`uuid` VARCHAR(36) PRIMARY KEY', $compiled);
    }

    #[Test]
    public function postgresql_compilation(): void
    {
        $statement = new UuidPrimaryKeyStatement('uuid');
        $compiled = $statement->compile(DatabaseDialect::POSTGRESQL);

        $this->assertSame('`uuid` UUID PRIMARY KEY', $compiled);
    }

    #[Test]
    public function sqlite_compilation(): void
    {
        $statement = new UuidPrimaryKeyStatement('uuid');
        $compiled = $statement->compile(DatabaseDialect::SQLITE);

        $this->assertSame('`uuid` TEXT PRIMARY KEY', $compiled);
    }

    #[Test]
    public function default_column_name(): void
    {
        $statement = new UuidPrimaryKeyStatement();
        $compiled = $statement->compile(DatabaseDialect::MYSQL);

        $this->assertSame('`id` VARCHAR(36) PRIMARY KEY', $compiled);
    }
}
