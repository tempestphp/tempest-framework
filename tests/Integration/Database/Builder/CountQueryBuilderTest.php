<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\CountQueryBuilder;
use Tempest\Database\Exceptions\CannotCountDistinctWithoutSpecifyingAColumn;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
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
            ->whereRaw('`title` = ?', 'Timeline Taxi')
            ->andWhereRaw('`index` <> ?', '1')
            ->orWhereRaw('`createdAt` > ?', '2025-01-01')
            ->build();

        $expected = 'SELECT COUNT(*) AS count FROM chapters WHERE title = ? AND index <> ? OR createdAt > ?';

        $sql = $query->compile();
        $bindings = $query->bindings;

        $this->assertSameWithoutBackticks($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_count_query_with_specified_asterisk(): void
    {
        $query = query('chapters')
            ->count('*')
            ->build();

        $sql = $query->compile();

        $expected = 'SELECT COUNT(*) AS `count` FROM `chapters`';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_count_query_with_specified_field(): void
    {
        $query = query('chapters')->count('title')->build();

        $sql = $query->compile();

        $expected = 'SELECT COUNT(`title`) AS `count` FROM `chapters`';

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

        $sql = $query->compile();

        $expected = 'SELECT COUNT(DISTINCT `title`) AS `count` FROM `chapters`';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_count_from_model(): void
    {
        $query = query(Author::class)->count()->build();

        $sql = $query->compile();

        $expected = 'SELECT COUNT(*) AS `count` FROM `authors`';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_count_query_with_conditions(): void
    {
        $query = query('chapters')
            ->count()
            ->when(
                true,
                fn (CountQueryBuilder $query) => $query
                    ->whereRaw('`title` = ?', 'Timeline Taxi')
                    ->andWhereRaw('`index` <> ?', '1')
                    ->orWhereRaw('`createdAt` > ?', '2025-01-01'),
            )
            ->when(
                false,
                fn (CountQueryBuilder $query) => $query
                    ->whereRaw('`title` = ?', 'Timeline Uber')
                    ->andWhereRaw('`index` <> ?', '2')
                    ->orWhereRaw('`createdAt` > ?', '2025-01-02'),
            )
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `chapters` WHERE `title` = ? AND `index` <> ? OR `createdAt` > ?';

        $sql = $query->compile();
        $bindings = $query->bindings;

        $this->assertSameWithoutBackticks($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_count(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')
            ->insert(
                ['id' => 1, 'name' => 'Brent'],
                ['id' => 2, 'name' => 'Other'],
            )
            ->execute();

        $count = query('authors')->count()->execute();

        $this->assertSame(2, $count);
    }

    public function test_multiple_where_raw(): void
    {
        $sql = query('books')
            ->count()
            ->whereRaw('title = ?', 'a')
            ->whereRaw('author_id = ?', 1)
            ->whereRaw('OR author_id = ?', 2)
            ->whereRaw('AND author_id <> NULL')
            ->compile();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE title = ? AND author_id = ? OR author_id = ? AND author_id <> NULL';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_multiple_where(): void
    {
        $sql = query('books')
            ->count()
            ->where('title', 'a')
            ->where('author_id', 1)
            ->compile();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.title = ? AND books.author_id = ?';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_where_in(): void
    {
        $query = query('books')
            ->count()
            ->whereIn('category', ['fiction', 'mystery', 'thriller'])
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.category IN (?,?,?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['fiction', 'mystery', 'thriller'], $query->bindings);
    }

    public function test_where_in_with_enum_class(): void
    {
        $query = query('books')
            ->count()
            ->whereIn('status', BookStatus::class)
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.status IN (?,?,?,?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['draft', 'published', 'archived', 'featured'], $query->bindings);
    }

    public function test_where_in_with_enums(): void
    {
        $query = query('books')
            ->count()
            ->whereIn('status', [BookStatus::PUBLISHED, BookStatus::FEATURED])
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.status IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['published', 'featured'], $query->bindings);
    }

    public function test_where_not_in(): void
    {
        $query = query('books')
            ->count()
            ->whereNotIn('status', ['draft', 'archived'])
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.status NOT IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['draft', 'archived'], $query->bindings);
    }

    public function test_where_not_in_with_enums(): void
    {
        $query = query('books')
            ->count()
            ->whereNotIn('status', [BookStatus::DRAFT, BookStatus::ARCHIVED])
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.status NOT IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['draft', 'archived'], $query->bindings);
    }

    public function test_where_between(): void
    {
        $query = query('books')
            ->count()
            ->whereBetween('publication_year', 2020, 2024)
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.publication_year BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([2020, 2024], $query->bindings);
    }

    public function test_where_not_between(): void
    {
        $query = query('books')
            ->count()
            ->whereNotBetween('price', 10.0, 50.0)
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.price NOT BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([10.0, 50.0], $query->bindings);
    }

    public function test_where_null(): void
    {
        $query = query('books')
            ->count()
            ->whereNull('deleted_at')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.deleted_at IS NULL';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([], $query->bindings);
    }

    public function test_where_not_null(): void
    {
        $query = query('books')
            ->count()
            ->whereNotNull('published_at')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published_at IS NOT NULL';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([], $query->bindings);
    }

    public function test_where_not(): void
    {
        $query = query('books')
            ->count()
            ->whereNot('status', 'draft')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.status != ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['draft'], $query->bindings);
    }

    public function test_where_like(): void
    {
        $query = query('books')
            ->count()
            ->whereLike('title', '%fantasy%')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.title LIKE ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['%fantasy%'], $query->bindings);
    }

    public function test_where_not_like(): void
    {
        $query = query('books')
            ->count()
            ->whereNotLike('title', '%test%')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.title NOT LIKE ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['%test%'], $query->bindings);
    }

    public function test_or_where_in(): void
    {
        $query = query('books')
            ->count()
            ->where('published', true)
            ->orWhereIn('category', ['fiction', 'mystery'])
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published = ? OR books.category IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 'fiction', 'mystery'], $query->bindings);
    }

    public function test_or_where_not_in(): void
    {
        $query = query('books')
            ->count()
            ->where('published', true)
            ->orWhereNotIn('status', ['draft', 'archived'])
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published = ? OR books.status NOT IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 'draft', 'archived'], $query->bindings);
    }

    public function test_or_where_between(): void
    {
        $query = query('books')
            ->count()
            ->where('published', true)
            ->orWhereBetween('rating', 4.0, 5.0)
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published = ? OR books.rating BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 4.0, 5.0], $query->bindings);
    }

    public function test_or_where_not_between(): void
    {
        $query = query('books')
            ->count()
            ->where('published', true)
            ->orWhereNotBetween('price', 20.0, 80.0)
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published = ? OR books.price NOT BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 20.0, 80.0], $query->bindings);
    }

    public function test_or_where_null(): void
    {
        $query = query('books')
            ->count()
            ->where('published', true)
            ->orWhereNull('deleted_at')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published = ? OR books.deleted_at IS NULL';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true], $query->bindings);
    }

    public function test_or_where_not_null(): void
    {
        $query = query('books')
            ->count()
            ->where('published', false)
            ->orWhereNotNull('featured_at')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published = ? OR books.featured_at IS NOT NULL';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([false], $query->bindings);
    }

    public function test_or_where_not(): void
    {
        $query = query('books')
            ->count()
            ->where('published', true)
            ->orWhereNot('status', 'archived')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published = ? OR books.status != ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 'archived'], $query->bindings);
    }

    public function test_or_where_like(): void
    {
        $query = query('books')
            ->count()
            ->where('published', true)
            ->orWhereLike('description', '%adventure%')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published = ? OR books.description LIKE ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, '%adventure%'], $query->bindings);
    }

    public function test_or_where_not_like(): void
    {
        $query = query('books')
            ->count()
            ->where('published', true)
            ->orWhereNotLike('title', '%boring%')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.published = ? OR books.title NOT LIKE ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, '%boring%'], $query->bindings);
    }

    public function test_chained_convenient_where_methods(): void
    {
        $query = query('books')
            ->count()
            ->whereIn('category', ['fiction', 'mystery'])
            ->whereNotNull('published_at')
            ->whereBetween('rating', 3.0, 5.0)
            ->whereNot('status', 'draft')
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.category IN (?,?) AND books.published_at IS NOT NULL AND books.rating BETWEEN ? AND ? AND books.status != ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['fiction', 'mystery', 3.0, 5.0, 'draft'], $query->bindings);
    }

    public function test_mixed_convenient_and_or_where_methods(): void
    {
        $query = query('books')
            ->count()
            ->whereIn('category', ['fiction'])
            ->orWhereNull('featured_at')
            ->orWhereNotBetween('price', 100.0, 200.0)
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.category IN (?) OR books.featured_at IS NULL OR books.price NOT BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['fiction', 100.0, 200.0], $query->bindings);
    }

    public function test_convenient_where_methods_in_groups(): void
    {
        $query = query('books')
            ->count()
            ->whereIn('status', ['published', 'featured'])
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereNotNull('published_at')
                    ->orWhereBetween('rating', 4.0, 5.0);
            })
            ->build();

        $expected = 'SELECT COUNT(*) AS `count` FROM `books` WHERE books.status IN (?,?) AND (books.published_at IS NOT NULL OR books.rating BETWEEN ? AND ?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['published', 'featured', 4.0, 5.0], $query->bindings);
    }

    public function test_nested_where_with_count_query(): void
    {
        $query = query('books')
            ->count()
            ->whereRaw('published = ?', true)
            ->orWhereGroup(function ($group): void {
                $group
                    ->whereRaw('status = ?', 'featured')
                    ->andWhereRaw('rating >= ?', 4.5);
            })
            ->build();

        $expected = 'SELECT COUNT(*) AS count FROM books WHERE published = ? OR (status = ? AND rating >= ?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 'featured', 4.5], $query->bindings);
    }

    public function test_count_query_builder_from_another_query_builder(): void
    {
        $query = query(Chapter::class)
            ->select()
            ->where('book_id', 1);

        $this->assertCount(1, CountQueryBuilder::fromQueryBuilder($query)->wheres);
    }
}

enum BookStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
    case FEATURED = 'featured';
}
