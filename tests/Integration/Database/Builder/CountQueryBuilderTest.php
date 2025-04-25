<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Exceptions\CannotCountDistinctWithoutSpecifyingAColumn;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class CountQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_simple_count_query(): void
    {
        $query = query('chapters')
            ->count()
            ->where('`title` = ?', 'Timeline Taxi')
            ->andWhere('`index` <> ?', '1')
            ->orWhere('`createdAt` > ?', '2025-01-01')
            ->build();

        $expected = <<<SQL
        SELECT COUNT(*)
        FROM `chapters`
        WHERE `title` = ?
        AND `index` <> ?
        OR `createdAt` > ?
        SQL;

        $sql = $query->getSql();
        $bindings = $query->bindings;

        $this->assertSame($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_count_query_with_alias(): void
    {
        $query = query('chapters')
            ->count()
            ->as('total')
            ->where('`title` = ?', 'Timeline Taxi')
            ->andWhere('`index` <> ?', '1')
            ->orWhere('`createdAt` > ?', '2025-01-01')
            ->build();

        $expected = <<<SQL
        SELECT COUNT(*) AS `total`
        FROM `chapters`
        WHERE `title` = ?
        AND `index` <> ?
        OR `createdAt` > ?
        SQL;

        $sql = $query->getSql();
        $bindings = $query->bindings;

        $this->assertSame($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_count_query_with_specified_asterisk(): void
    {
        $query = query('chapters')
            ->count('*')
            ->build();

        $sql = $query->getSql();

        $expected = <<<SQL
        SELECT COUNT(*)
        FROM `chapters`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_count_query_with_specified_field(): void
    {
        $query = query('chapters')->count('title')->build();

        $sql = $query->getSql();

        $expected = <<<SQL
        SELECT COUNT(`title`)
        FROM `chapters`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_count_query_with_specified_field_and_alias(): void
    {
        $query = query('chapters')
            ->count('title')
            ->as('total')
            ->build();

        $sql = $query->getSql();

        $expected = <<<SQL
        SELECT COUNT(`title`) AS `total`
        FROM `chapters`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_count_query_without_specifying_column_cannot_be_distinct(): void
    {
        $this->expectException(CannotCountDistinctWithoutSpecifyingAColumn::class);

        query('chapters')
            ->count()
            ->distinct()
            ->build();
    }

    public function test_count_query_with_specified_asterisk_cannot_be_distinct(): void
    {
        $this->expectException(CannotCountDistinctWithoutSpecifyingAColumn::class);

        query('chapters')
            ->count('*')
            ->distinct()
            ->build();
    }

    public function test_count_query_with_distinct_specified_field(): void
    {
        $query = query('chapters')
            ->count('title')
            ->distinct()
            ->build();

        $sql = $query->getSql();

        $expected = <<<SQL
        SELECT COUNT(DISTINCT `title`)
        FROM `chapters`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_count_query_with_distinct_specified_field_and_alias(): void
    {
        $query = query('chapters')
            ->count('title')
            ->as('total')
            ->distinct()
            ->build();

        $sql = $query->getSql();

        $expected = <<<SQL
        SELECT COUNT(DISTINCT `title`) AS `total`
        FROM `chapters`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_count_from_model(): void
    {
        $query = query(Author::class)->count()->build();

        $sql = $query->getSql();

        $expected = <<<SQL
        SELECT COUNT(*)
        FROM `authors`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_count_query_with_conditions(): void
    {
        $query = query('chapters')
            ->count()
            ->when(
                true,
                fn (CountQueryBuilder $query) => $query
                    ->where('`title` = ?', 'Timeline Taxi')
                    ->andWhere('`index` <> ?', '1')
                    ->orWhere('`createdAt` > ?', '2025-01-01'),
            )
            ->when(
                false,
                fn (CountQueryBuilder $query) => $query
                    ->where('`title` = ?', 'Timeline Uber')
                    ->andWhere('`index` <> ?', '2')
                    ->orWhere('`createdAt` > ?', '2025-01-02'),
            )
            ->build();

        $expected = <<<SQL
        SELECT COUNT(*)
        FROM `chapters`
        WHERE `title` = ?
        AND `index` <> ?
        OR `createdAt` > ?
        SQL;

        $sql = $query->getSql();
        $bindings = $query->bindings;

        $this->assertSame($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }
}
