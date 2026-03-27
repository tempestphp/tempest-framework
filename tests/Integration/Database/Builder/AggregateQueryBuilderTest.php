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
