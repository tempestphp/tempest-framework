<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\DateTime\DateTime;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class ConvenientWhereMethodsTest extends FrameworkIntegrationTestCase
{
    public function test_select_where_in(): void
    {
        $query = query('books')
            ->select()
            ->whereIn('category', ['fiction', 'mystery', 'thriller'])
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.category IN (?,?,?)';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['fiction', 'mystery', 'thriller'], $query->bindings);
    }

    public function test_select_where_not_in(): void
    {
        $query = query('books')
            ->select()
            ->whereNotIn('status', ['draft', 'archived'])
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.status NOT IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['draft', 'archived'], $query->bindings);
    }

    public function test_select_where_between(): void
    {
        $query = query('books')
            ->select()
            ->whereBetween('publication_year', 2020, 2024)
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.publication_year BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([2020, 2024], $query->bindings);
    }

    public function test_select_where_not_between(): void
    {
        $query = query('books')
            ->select()
            ->whereNotBetween('price', 10.0, 50.0)
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.price NOT BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([10.0, 50.0], $query->bindings);
    }

    public function test_select_where_null(): void
    {
        $query = query('books')
            ->select()
            ->whereNull('deleted_at')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.deleted_at IS NULL';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([], $query->bindings);
    }

    public function test_select_where_not_null(): void
    {
        $query = query('books')
            ->select()
            ->whereNotNull('published_at')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.published_at IS NOT NULL';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([], $query->bindings);
    }

    public function test_select_where_not(): void
    {
        $query = query('books')
            ->select()
            ->whereNot('status', 'draft')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.status != ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['draft'], $query->bindings);
    }

    public function test_select_where_like(): void
    {
        $query = query('books')
            ->select()
            ->whereLike('title', '%fantasy%')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.title LIKE ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['%fantasy%'], $query->bindings);
    }

    public function test_select_where_not_like(): void
    {
        $query = query('books')
            ->select()
            ->whereNotLike('title', '%test%')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.title NOT LIKE ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['%test%'], $query->bindings);
    }

    public function test_update_where_in(): void
    {
        $query = query('books')
            ->update(title: 'New Title')
            ->whereIn('category', ['fiction', 'mystery'])
            ->build();

        $expected = 'UPDATE `books` SET title = ? WHERE books.category IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['New Title', 'fiction', 'mystery'], $query->bindings);
    }

    public function test_update_where_between(): void
    {
        $query = query('books')
            ->update(status: 'updated')
            ->whereBetween('rating', 3.0, 5.0)
            ->build();

        $expected = 'UPDATE `books` SET status = ? WHERE books.rating BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['updated', 3.0, 5.0], $query->bindings);
    }

    public function test_update_where_null(): void
    {
        $query = query('books')
            ->update(status: 'archived')
            ->whereNull('deleted_at')
            ->build();

        $expected = 'UPDATE `books` SET status = ? WHERE books.deleted_at IS NULL';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['archived'], $query->bindings);
    }

    public function test_delete_where_in(): void
    {
        $query = query('books')
            ->delete()
            ->whereIn('status', ['draft', 'archived'])
            ->build();

        $expected = 'DELETE FROM `books` WHERE books.status IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['draft', 'archived'], $query->bindings);
    }

    public function test_delete_where_not_null(): void
    {
        $query = query('books')
            ->delete()
            ->whereNotNull('deleted_at')
            ->build();

        $expected = 'DELETE FROM `books` WHERE books.deleted_at IS NOT NULL';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([], $query->bindings);
    }

    public function test_complex_chaining_with_convenient_methods(): void
    {
        $query = query('books')
            ->select()
            ->whereIn('category', ['fiction', 'mystery'])
            ->whereNotNull('published_at')
            ->whereBetween('rating', 3.0, 5.0)
            ->orWhereNot('status', 'draft')
            ->orWhereLike('title', '%bestseller%')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.category IN (?,?) AND books.published_at IS NOT NULL AND books.rating BETWEEN ? AND ? OR books.status != ? OR books.title LIKE ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['fiction', 'mystery', 3.0, 5.0, 'draft', '%bestseller%'], $query->bindings);
    }

    public function test_convenient_methods_in_where_groups(): void
    {
        $query = query('books')
            ->select()
            ->where('published', true)
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereIn('category', ['fiction', 'mystery'])
                    ->orWhereNull('featured_at');
            })
            ->orWhereGroup(function ($group): void {
                $group
                    ->whereBetween('rating', 4.0, 5.0)
                    ->whereNotLike('title', '%draft%');
            })
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.published = ? AND (books.category IN (?,?) OR books.featured_at IS NULL) OR (books.rating BETWEEN ? AND ? AND books.title NOT LIKE ?)';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([true, 'fiction', 'mystery', 4.0, 5.0, '%draft%'], $query->bindings);
    }

    public function test_nested_where_groups_with_convenient_methods(): void
    {
        $query = query('books')
            ->select()
            ->whereIn('status', ['published', 'featured'])
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereNotNull('published_at')
                    ->orWhereGroup(function ($innerGroup): void {
                        $innerGroup
                            ->whereBetween('rating', 4.0, 5.0)
                            ->whereNotIn('category', ['children']);
                    });
            })
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.status IN (?,?) AND (books.published_at IS NOT NULL OR (books.rating BETWEEN ? AND ? AND books.category NOT IN (?)))';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['published', 'featured', 4.0, 5.0, 'children'], $query->bindings);
    }

    // Note: Validation error tests removed since API changed to two separate arguments

    public function test_all_convenient_methods_together(): void
    {
        $query = query('books')
            ->select()
            ->whereIn('category', ['fiction'])
            ->whereNotIn('status', ['draft'])
            ->whereBetween('rating', 3.0, 5.0)
            ->whereNotBetween('price', 100.0, 200.0)
            ->whereNull('deleted_at')
            ->whereNotNull('published_at')
            ->whereNot('featured', false)
            ->whereLike('title', '%adventure%')
            ->whereNotLike('description', '%boring%')
            ->orWhereIn('tags', ['bestseller'])
            ->orWhereNotIn('awards', ['none'])
            ->orWhereBetween('pages', 200, 400)
            ->orWhereNotBetween('weight', 2.0, 5.0)
            ->orWhereNull('special_edition')
            ->orWhereNotNull('isbn')
            ->orWhereNot('limited_edition', true)
            ->orWhereLike('publisher', '%Penguin%')
            ->orWhereNotLike('format', '%digital%')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE books.category IN (?) AND books.status NOT IN (?) AND books.rating BETWEEN ? AND ? AND books.price NOT BETWEEN ? AND ? AND books.deleted_at IS NULL AND books.published_at IS NOT NULL AND books.featured != ? AND books.title LIKE ? AND books.description NOT LIKE ? OR books.tags IN (?) OR books.awards NOT IN (?) OR books.pages BETWEEN ? AND ? OR books.weight NOT BETWEEN ? AND ? OR books.special_edition IS NULL OR books.isbn IS NOT NULL OR books.limited_edition != ? OR books.publisher LIKE ? OR books.format NOT LIKE ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([
            'fiction',
            'draft',
            3.0,
            5.0,
            100.0,
            200.0,
            false,
            '%adventure%',
            '%boring%',
            'bestseller',
            'none',
            200,
            400,
            2.0,
            5.0,
            true,
            '%Penguin%',
            '%digital%',
        ], $query->bindings);
    }

    public function test_where_between_with_tempest_datetime(): void
    {
        $startDate = DateTime::parse('2024-01-01 00:00:00');
        $endDate = DateTime::parse('2024-12-31 23:59:59');

        $query = query('events')
            ->select()
            ->whereBetween('created_at', $startDate, $endDate)
            ->build();

        $expected = 'SELECT * FROM `events` WHERE events.created_at BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([$startDate, $endDate], $query->bindings);
    }

    public function test_where_between_with_mixed_datetime_types(): void
    {
        $startDate = DateTime::parse('2024-01-01 00:00:00');
        $endDate = '2024-12-31 23:59:59';

        $query = query('events')
            ->select()
            ->whereBetween('created_at', $startDate, $endDate)
            ->build();

        $expected = 'SELECT * FROM `events` WHERE events.created_at BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([$startDate, $endDate], $query->bindings);
    }

    public function test_where_not_between_with_tempest_datetime(): void
    {
        $startDate = DateTime::parse('2024-06-01 00:00:00');
        $endDate = DateTime::parse('2024-08-31 23:59:59');

        $query = query('events')
            ->select()
            ->whereNotBetween('created_at', $startDate, $endDate)
            ->build();

        $expected = 'SELECT * FROM `events` WHERE events.created_at NOT BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([$startDate, $endDate], $query->bindings);
    }

    public function test_or_where_between_with_tempest_datetime(): void
    {
        $startDate = DateTime::parse('2024-01-01 00:00:00');
        $endDate = DateTime::parse('2024-03-31 23:59:59');

        $query = query('events')
            ->select()
            ->where('status', 'active')
            ->orWhereBetween('created_at', $startDate, $endDate)
            ->build();

        $expected = 'SELECT * FROM `events` WHERE events.status = ? OR events.created_at BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['active', $startDate, $endDate], $query->bindings);
    }

    public function test_or_where_not_between_with_tempest_datetime(): void
    {
        $startDate = DateTime::parse('2024-07-01 00:00:00');
        $endDate = DateTime::parse('2024-09-30 23:59:59');

        $query = query('events')
            ->select()
            ->where('priority', 'high')
            ->orWhereNotBetween('created_at', $startDate, $endDate)
            ->build();

        $expected = 'SELECT * FROM `events` WHERE events.priority = ? OR events.created_at NOT BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['high', $startDate, $endDate], $query->bindings);
    }

    public function test_where_between_with_datetime_convenience_methods(): void
    {
        $today = DateTime::now();
        $startDate = $today->startOfDay();
        $endDate = $today->endOfDay();

        $query = query('events')
            ->select()
            ->whereBetween('created_at', $startDate, $endDate)
            ->build();

        $expected = 'SELECT * FROM `events` WHERE events.created_at BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([$startDate, $endDate], $query->bindings);
    }

    public function test_where_between_with_datetime_start_and_end_of_month(): void
    {
        $today = DateTime::now();
        $startDate = $today->startOfMonth();
        $endDate = $today->endOfMonth();

        $query = query('events')
            ->select()
            ->whereBetween('created_at', $startDate, $endDate)
            ->build();

        $expected = 'SELECT * FROM `events` WHERE events.created_at BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([$startDate, $endDate], $query->bindings);
    }

    public function test_where_between_with_datetime_start_and_end_of_week(): void
    {
        // Use a safe date in the middle of the month to avoid edge cases
        $baseDate = DateTime::parse('2024-08-15 12:00:00');
        $startDate = $baseDate->startOfWeek();
        $endDate = $baseDate->endOfWeek();

        $query = query('events')
            ->select()
            ->whereBetween('created_at', $startDate, $endDate)
            ->build();

        $expected = 'SELECT * FROM `events` WHERE events.created_at BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame([$startDate, $endDate], $query->bindings);
    }
}
