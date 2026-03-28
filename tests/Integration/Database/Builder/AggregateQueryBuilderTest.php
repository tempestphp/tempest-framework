<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\QueryStatements\FieldStatement;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\Support\arr;

/**
 * @internal
 */
final class AggregateQueryBuilderTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function sum_compiles_correct_sql(): void
    {
        $query = SelectQueryBuilder::fromQueryBuilder(
            source: query(model: 'books')->select(),
            fields: arr(input: [new FieldStatement(field: 'SUM(`price`) AS `sum`')]),
        )->build();

        $this->assertSameWithoutBackticks(expected: 'SELECT SUM(`price`) AS `sum` FROM `books`', actual: $query->compile());
    }

    #[Test]
    public function avg_compiles_correct_sql(): void
    {
        $query = SelectQueryBuilder::fromQueryBuilder(
            source: query(model: 'books')->select(),
            fields: arr(input: [new FieldStatement(field: 'AVG(`price`) AS `avg`')]),
        )->build();

        $this->assertSameWithoutBackticks(expected: 'SELECT AVG(`price`) AS `avg` FROM `books`', actual: $query->compile());
    }

    #[Test]
    public function max_compiles_correct_sql(): void
    {
        $query = SelectQueryBuilder::fromQueryBuilder(
            source: query(model: 'books')->select(),
            fields: arr(input: [new FieldStatement(field: 'MAX(`price`) AS `max`')]),
        )->build();

        $this->assertSameWithoutBackticks(expected: 'SELECT MAX(`price`) AS `max` FROM `books`', actual: $query->compile());
    }

    #[Test]
    public function min_compiles_correct_sql(): void
    {
        $query = SelectQueryBuilder::fromQueryBuilder(
            source: query(model: 'books')->select(),
            fields: arr(input: [new FieldStatement(field: 'MIN(`price`) AS `min`')]),
        )->build();

        $this->assertSameWithoutBackticks(expected: 'SELECT MIN(`price`) AS `min` FROM `books`', actual: $query->compile());
    }

    #[Test]
    public function sum_with_where_compiles_correct_sql(): void
    {
        $query = SelectQueryBuilder::fromQueryBuilder(
            source: query(model: 'books')->select()->where('author_id', 1),
            fields: arr(input: [new FieldStatement(field: 'SUM(`price`) AS `sum`')]),
        )->build();

        $this->assertSameWithoutBackticks(expected: 'SELECT SUM(`price`) AS `sum` FROM `books` WHERE books.author_id = ?', actual: $query->compile());
    }

    #[Test]
    public function sum_from_model(): void
    {
        $query = SelectQueryBuilder::fromQueryBuilder(
            source: query(model: Author::class)->select(),
            fields: arr(input: [new FieldStatement(field: 'SUM(`id`) AS `sum`')]),
        )->build();

        $this->assertSameWithoutBackticks(expected: 'SELECT SUM(`id`) AS `sum` FROM `authors`', actual: $query->compile());
    }

    #[Test]
    public function sum_returns_correct_value(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $this->assertSame(6, query(model: Author::class)->sum(column: 'id'));
    }

    #[Test]
    public function avg_returns_correct_value(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $this->assertSame(2.0, query(model: Author::class)->avg(column: 'id'));
    }

    #[Test]
    public function max_returns_correct_value(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $this->assertSame(3, query(model: Author::class)->max(column: 'id'));
    }

    #[Test]
    public function min_returns_correct_value(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $this->assertSame(1, query(model: Author::class)->min(column: 'id'));
    }

    #[Test]
    public function sum_with_where_condition(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $this->assertSame(1, query(model: Author::class)->find(name: 'Author A')->sum(column: 'id'));
    }

    #[Test]
    public function max_on_string_column(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::create(name: 'Alpha');
        Author::create(name: 'Zeta');
        Author::create(name: 'Beta');

        $this->assertSame('Zeta', query(model: Author::class)->max(column: 'name'));
    }

    #[Test]
    public function min_on_string_column(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::create(name: 'Alpha');
        Author::create(name: 'Zeta');
        Author::create(name: 'Beta');

        $this->assertSame('Alpha', query(model: Author::class)->min(column: 'name'));
    }

    #[Test]
    public function sum_static_shortcut_on_model(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');

        $this->assertSame(3, Author::sum(column: 'id'));
    }

    #[Test]
    public function avg_static_shortcut_on_model(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');

        $this->assertSame(1.5, Author::avg(column: 'id'));
    }
}
