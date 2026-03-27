<<<<<<< ours
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
|||||||
=======
<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\AggregateFunction;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\QueryStatements\FieldStatement;
use Tempest\Support\Arr\ImmutableArray;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class AggregateQueryBuilderTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function sum_compiles_correct_sql(): void
    {
        $query = $this->buildAggregate(
            builder: query('books')->select(),
            function: AggregateFunction::SUM,
            column: 'price',
        );

        $expected = 'SELECT SUM(`price`) AS `sum` FROM `books`';

        $this->assertSameWithoutBackticks($expected, $query->compile());
    }

    #[Test]
    public function avg_compiles_correct_sql(): void
    {
        $query = $this->buildAggregate(
            builder: query('books')->select(),
            function: AggregateFunction::AVG,
            column: 'price',
        );

        $expected = 'SELECT AVG(`price`) AS `avg` FROM `books`';

        $this->assertSameWithoutBackticks($expected, $query->compile());
    }

    #[Test]
    public function max_compiles_correct_sql(): void
    {
        $query = $this->buildAggregate(
            builder: query('books')->select(),
            function: AggregateFunction::MAX,
            column: 'price',
        );

        $expected = 'SELECT MAX(`price`) AS `max` FROM `books`';

        $this->assertSameWithoutBackticks($expected, $query->compile());
    }

    #[Test]
    public function min_compiles_correct_sql(): void
    {
        $query = $this->buildAggregate(
            builder: query('books')->select(),
            function: AggregateFunction::MIN,
            column: 'price',
        );

        $expected = 'SELECT MIN(`price`) AS `min` FROM `books`';

        $this->assertSameWithoutBackticks($expected, $query->compile());
    }

    #[Test]
    public function sum_with_where_compiles_correct_sql(): void
    {
        $query = $this->buildAggregate(
            builder: query('books')->select()->where('author_id', 1),
            function: AggregateFunction::SUM,
            column: 'price',
        );

        $expected = 'SELECT SUM(`price`) AS `sum` FROM `books` WHERE books.author_id = ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
    }

    #[Test]
    public function sum_from_model(): void
    {
        $query = $this->buildAggregate(
            builder: query(Author::class)->select(),
            function: AggregateFunction::SUM,
            column: 'id',
        );

        $expected = 'SELECT SUM(`id`) AS `sum` FROM `authors`';

        $this->assertSameWithoutBackticks($expected, $query->compile());
    }

    #[Test]
    public function sum_returns_correct_value(): void
    {
        $this->database->migrate(
            \Tempest\Database\Migrations\CreateMigrationsTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreatePublishersTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $sum = query(Author::class)->sum('id');

        $this->assertSame(6, $sum);
    }

    #[Test]
    public function avg_returns_correct_value(): void
    {
        $this->database->migrate(
            \Tempest\Database\Migrations\CreateMigrationsTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreatePublishersTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $avg = query(Author::class)->avg('id');

        $this->assertSame(2.0, $avg);
    }

    #[Test]
    public function max_returns_correct_value(): void
    {
        $this->database->migrate(
            \Tempest\Database\Migrations\CreateMigrationsTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreatePublishersTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $max = query(Author::class)->max('id');

        $this->assertSame(3, $max);
    }

    #[Test]
    public function min_returns_correct_value(): void
    {
        $this->database->migrate(
            \Tempest\Database\Migrations\CreateMigrationsTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreatePublishersTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $min = query(Author::class)->min('id');

        $this->assertSame(1, $min);
    }

    #[Test]
    public function sum_with_where_condition(): void
    {
        $this->database->migrate(
            \Tempest\Database\Migrations\CreateMigrationsTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreatePublishersTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');
        Author::create(name: 'Author C');

        $sum = query(Author::class)->find(name: 'Author A')->sum('id');

        $this->assertSame(1, $sum);
    }

    #[Test]
    public function max_on_string_column(): void
    {
        $this->database->migrate(
            \Tempest\Database\Migrations\CreateMigrationsTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreatePublishersTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreateAuthorTable::class,
        );

        Author::create(name: 'Alpha');
        Author::create(name: 'Zeta');
        Author::create(name: 'Beta');

        $max = query(Author::class)->max('name');

        $this->assertSame('Zeta', $max);
    }

    #[Test]
    public function min_on_string_column(): void
    {
        $this->database->migrate(
            \Tempest\Database\Migrations\CreateMigrationsTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreatePublishersTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreateAuthorTable::class,
        );

        Author::create(name: 'Alpha');
        Author::create(name: 'Zeta');
        Author::create(name: 'Beta');

        $min = query(Author::class)->min('name');

        $this->assertSame('Alpha', $min);
    }

    #[Test]
    public function sum_static_shortcut_on_model(): void
    {
        $this->database->migrate(
            \Tempest\Database\Migrations\CreateMigrationsTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreatePublishersTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');

        $sum = Author::sum('id');

        $this->assertSame(3, $sum);
    }

    #[Test]
    public function avg_static_shortcut_on_model(): void
    {
        $this->database->migrate(
            \Tempest\Database\Migrations\CreateMigrationsTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreatePublishersTable::class,
            \Tests\Tempest\Fixtures\Migrations\CreateAuthorTable::class,
        );

        Author::create(name: 'Author A');
        Author::create(name: 'Author B');

        $avg = Author::avg('id');

        $this->assertSame(1.5, $avg);
    }

    private function buildAggregate(
        SelectQueryBuilder $builder,
        AggregateFunction $function,
        string $column,
    ): \Tempest\Database\Query {
        $key = strtolower($function->value);

        $field = new FieldStatement(
            field: sprintf('%s(`%s`) AS `%s`', $function->value, $column, $key),
        );

        return SelectQueryBuilder::fromQueryBuilder(
            $builder,
            fields: new ImmutableArray([$field]),
        )->build();
    }
}
>>>>>>> theirs
