<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\QueryStatements;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Direction;
use Tempest\Database\QueryStatements\OrderByStatement;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class OrderByStatementTest extends FrameworkIntegrationTestCase
{
    #[Test]
    #[TestWith([DatabaseDialect::MYSQL, '`books`.`title` ASC'])]
    #[TestWith([DatabaseDialect::SQLITE, '`books`.`title` ASC'])]
    #[TestWith([DatabaseDialect::POSTGRESQL, '"books"."title" ASC'])]
    public function qualified_field_is_quoted_per_dialect(DatabaseDialect $dialect, string $expected): void
    {
        $statement = new OrderByStatement(field: 'books.title', direction: Direction::ASC);

        $this->assertSame(expected: $expected, actual: $statement->compile(dialect: $dialect));
    }

    #[Test]
    #[TestWith([DatabaseDialect::MYSQL, '`title` DESC'])]
    #[TestWith([DatabaseDialect::SQLITE, '`title` DESC'])]
    #[TestWith([DatabaseDialect::POSTGRESQL, '"title" DESC'])]
    public function bare_column_is_quoted_per_dialect(DatabaseDialect $dialect, string $expected): void
    {
        $statement = new OrderByStatement(field: 'title', direction: Direction::DESC);

        $this->assertSame(expected: $expected, actual: $statement->compile(dialect: $dialect));
    }
}
