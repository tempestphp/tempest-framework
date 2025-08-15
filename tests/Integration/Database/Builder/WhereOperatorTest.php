<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use InvalidArgumentException;
use Tempest\Database\Builder\WhereOperator;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class WhereOperatorTest extends FrameworkIntegrationTestCase
{
    public function test_hybrid_where_equals(): void
    {
        $query = query('books')
            ->select()
            ->where('`title` = ?', 'Timeline Taxi')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE `title` = ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['Timeline Taxi'], $query->bindings);
    }

    public function test_hybrid_where_superior(): void
    {
        $query = query('books')
            ->select()
            ->where('`price` > ?', 20)
            ->build();

        $expected = 'SELECT * FROM `books` WHERE `price` > ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([20], $query->bindings);
    }

    public function test_hybrid_where_field(): void
    {
        $query = query('books')
            ->select()
            ->where('title', 'Timeline Taxi')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE `books.title` = ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['Timeline Taxi'], $query->bindings);
    }

    public function test_hybrid_where_with_operator(): void
    {
        $query = query('books')
            ->select()
            ->where('rating', 4.0, WhereOperator::GREATER_THAN)
            ->build();

        $expected = 'SELECT * FROM `books` WHERE `books.rating` > ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([4.0], $query->bindings);
    }

    public function test_where_field(): void
    {
        $query = query('books')
            ->select()
            ->whereField('title', 'Timeline Taxi')
            ->build();

        $expected = 'SELECT * FROM `books` WHERE `books.title` = ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['Timeline Taxi'], $query->bindings);
    }

    public function test_where_field_explicit_operator(): void
    {
        $query = query('books')
            ->select()
            ->whereField('rating', 4.0, WhereOperator::GREATER_THAN)
            ->build();

        $expected = 'SELECT * FROM books WHERE books.rating > ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([4.0], $query->bindings);
    }

    public function test_basic_where_with_field_and_value(): void
    {
        $query = query('books')
            ->select()
            ->where('title', 'Test Book')
            ->build();

        $expected = 'SELECT * FROM books WHERE books.title = ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['Test Book'], $query->bindings);
    }

    public function test_where_with_string_operator(): void
    {
        $query = query('books')
            ->select()
            ->whereField('title', '%fantasy%', 'like')
            ->build();

        $expected = 'SELECT * FROM books WHERE books.title LIKE ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['%fantasy%'], $query->bindings);
    }

    public function test_where_in_operator(): void
    {
        $query = query('books')
            ->select()
            ->whereField('category', ['fiction', 'mystery', 'thriller'], WhereOperator::IN)
            ->build();

        $expected = 'SELECT * FROM books WHERE books.category IN (?,?,?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['fiction', 'mystery', 'thriller'], $query->bindings);
    }

    public function test_where_between_operator(): void
    {
        $query = query('books')
            ->select()
            ->whereField('publication_year', [2020, 2024], WhereOperator::BETWEEN)
            ->build();

        $expected = 'SELECT * FROM books WHERE books.publication_year BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([2020, 2024], $query->bindings);
    }

    public function test_where_is_null_operator(): void
    {
        $query = query('books')
            ->select()
            ->whereField('deleted_at', null, WhereOperator::IS_NULL)
            ->build();

        $expected = 'SELECT * FROM books WHERE books.deleted_at IS NULL';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([], $query->bindings);
    }

    public function test_multiple_where_conditions(): void
    {
        $query = query('books')
            ->select()
            ->where('published', true)
            ->andWhere('rating', 4.0, WhereOperator::GREATER_THAN_OR_EQUAL)
            ->orWhere('category', 'bestseller')
            ->build();

        $expected = 'SELECT * FROM books WHERE books.published = ? AND books.rating >= ? OR books.category = ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 4.0, 'bestseller'], $query->bindings);
    }

    public function test_where_raw_for_complex_conditions(): void
    {
        $query = query('books')
            ->select()
            ->where('published', true)
            ->andWhereRaw('(title LIKE ? OR description LIKE ?)', '%test%', '%test%')
            ->build();

        $expected = 'SELECT * FROM books WHERE books.published = ? AND (title LIKE ? OR description LIKE ?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, '%test%', '%test%'], $query->bindings);
    }

    public function test_nested_where_groups_with_new_api(): void
    {
        $query = query('books')
            ->select()
            ->where('published', true)
            ->andWhereGroup(function ($group): void {
                $group
                    ->where('category', 'fiction')
                    ->orWhere('rating', 4.5, WhereOperator::GREATER_THAN);
            })
            ->build();

        $expected = 'SELECT * FROM books WHERE books.published = ? AND (books.category = ? OR books.rating > ?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 'fiction', 4.5], $query->bindings);
    }

    public function test_mixed_raw_and_typed_conditions_in_groups(): void
    {
        $query = query('books')
            ->select()
            ->where('status', 'published')
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereField('category', ['fiction', 'mystery'], WhereOperator::IN)
                    ->orWhereRaw('custom_field IS NOT NULL');
            })
            ->build();

        $expected = 'SELECT * FROM books WHERE books.status = ? AND (books.category IN (?,?) OR custom_field IS NOT NULL)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['published', 'fiction', 'mystery'], $query->bindings);
    }

    public function test_error_handling_for_in_operator_without_array(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('IN operator requires an array of values');

        query('books')
            ->select()
            ->whereField('category', 'fiction', WhereOperator::IN)
            ->build();
    }

    public function test_error_handling_for_between_operator_with_wrong_array_size(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('BETWEEN operator requires an array with exactly 2 values');

        query('books')
            ->select()
            ->whereField('year', [2020, 2021, 2022], WhereOperator::BETWEEN)
            ->build();
    }
}
