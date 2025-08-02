<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Exceptions\CannotCountDistinctWithoutSpecifyingAColumn;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
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
        SELECT COUNT(*) AS `count`
        FROM `chapters`
        WHERE `title` = ?
        AND `index` <> ?
        OR `createdAt` > ?
        SQL;

        $sql = $query->toSql();
        $bindings = $query->bindings;

        $this->assertSameWithoutBackticks($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_count_query_with_specified_asterisk(): void
    {
        $query = query('chapters')
            ->count('*')
            ->build();

        $sql = $query->toSql();

        $expected = <<<SQL
        SELECT COUNT(*) AS `count`
        FROM `chapters`
        SQL;

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_count_query_with_specified_field(): void
    {
        $query = query('chapters')->count('title')->build();

        $sql = $query->toSql();

        $expected = <<<SQL
        SELECT COUNT(`title`) AS `count`
        FROM `chapters`
        SQL;

        $this->assertSameWithoutBackticks($expected, $sql);
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

        $sql = $query->toSql();

        $expected = <<<SQL
        SELECT COUNT(DISTINCT `title`) AS `count`
        FROM `chapters`
        SQL;

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_count_from_model(): void
    {
        $query = query(Author::class)->count()->build();

        $sql = $query->toSql();

        $expected = <<<SQL
        SELECT COUNT(*) AS `count`
        FROM `authors`
        SQL;

        $this->assertSameWithoutBackticks($expected, $sql);
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
        SELECT COUNT(*) AS `count`
        FROM `chapters`
        WHERE `title` = ?
        AND `index` <> ?
        OR `createdAt` > ?
        SQL;

        $sql = $query->toSql();
        $bindings = $query->bindings;

        $this->assertSameWithoutBackticks($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_count(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')->insert(
            ['id' => 1, 'name' => 'Brent'],
            ['id' => 2, 'name' => 'Other'],
        )->execute();

        $count = query('authors')->count()->execute();

        $this->assertSame(2, $count);
    }

    public function test_multiple_where(): void
    {
        $sql = query('books')
            ->count()
            ->where('title = ?', 'a')
            ->where('author_id = ?', 1)
            ->where('OR author_id = ?', 2)
            ->where('AND author_id <> NULL')
            ->toSql();

        $expected = <<<SQL
        SELECT COUNT(*) AS `count`
        FROM `books`
        WHERE title = ?
        AND author_id = ?
        OR author_id = ?
        AND author_id <> NULL
        SQL;

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_multiple_where_field(): void
    {
        $sql = query('books')
            ->count()
            ->whereField('title', 'a')
            ->whereField('author_id', 1)
            ->toSql();

        $expected = <<<SQL
        SELECT COUNT(*) AS `count`
        FROM `books`
        WHERE books.title = ?
        AND books.author_id = ?
        SQL;

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_tap(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')->insert(
            ['id' => 1, 'name' => 'Brent'],
            ['id' => 2, 'name' => 'Other'],
        )->execute();

        $count = query('authors')
            ->count()
            ->tap(fn (CountQueryBuilder $query) => $query->where('id = ?', 1))
            ->execute();

        $this->assertSame(1, $count);
    }
}
