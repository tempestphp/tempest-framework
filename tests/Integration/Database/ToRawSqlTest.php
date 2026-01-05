<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database;

use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class ToRawSqlTest extends FrameworkIntegrationTestCase
{
    public function test_select_query_to_raw_sql_without_bindings(): void
    {
        $this->assertSameWithoutBackticks(
            expected: 'SELECT * FROM books',
            actual: query('books')->select()->toRawSql()->toString(),
        );
    }

    public function test_select_query_to_raw_sql_with_where_clause(): void
    {
        $this->assertSameWithoutBackticks(
            expected: "SELECT * FROM books WHERE books.title = 'The Hobbit'",
            actual: query('books')->select()->where('title', 'The Hobbit')->toRawSql()->toString(),
        );
    }

    public function test_select_query_to_raw_sql_with_multiple_bindings(): void
    {
        $this->assertSameWithoutBackticks(
            expected: "SELECT title, author_id FROM books WHERE books.title = 'The Hobbit' AND books.author_id = 1 AND books.category IN ('fantasy','adventure')",
            actual: query('books')
                ->select('title', 'author_id')
                ->where('title', 'The Hobbit')
                ->where('author_id', 1)
                ->whereIn('category', ['fantasy', 'adventure'])
                ->toRawSql()
                ->toString(),
        );
    }

    public function test_select_query_to_raw_sql_with_raw_where(): void
    {
        $this->assertSameWithoutBackticks(
            expected: "SELECT * FROM books WHERE published_date > '2020-01-01' AND rating >= 4.5",
            actual: query('books')
                ->select()
                ->where('published_date > ?', '2020-01-01')
                ->where('rating >= ?', 4.5)
                ->toRawSql()
                ->toString(),
        );
    }

    public function test_select_query_to_raw_sql_with_boolean_values(): void
    {
        $rawSql = query('books')
            ->select()
            ->where('published', true)
            ->where('featured', false)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'published', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'featured', haystack: $rawSql);
    }

    public function test_select_query_to_raw_sql_with_null_values(): void
    {
        $this->assertSameWithoutBackticks(
            expected: 'SELECT * FROM books WHERE books.deleted_at IS NULL AND books.published_at IS NOT NULL',
            actual: query('books')
                ->select()
                ->whereNull('deleted_at')
                ->whereNotNull('published_at')
                ->toRawSql()
                ->toString(),
        );
    }

    public function test_select_query_to_raw_sql_with_numeric_values(): void
    {
        $this->assertSameWithoutBackticks(
            expected: 'SELECT * FROM books WHERE books.price = 29.99 AND books.pages = 350',
            actual: query('books')
                ->select()
                ->where('price', 29.99)
                ->where('pages', 350)
                ->toRawSql()
                ->toString(),
        );
    }

    public function test_select_query_to_raw_sql_with_between_clause(): void
    {
        $this->assertSameWithoutBackticks(
            expected: 'SELECT * FROM books WHERE books.price BETWEEN 10 AND 50',
            actual: query('books')
                ->select()
                ->whereBetween('price', 10.0, 50.0)
                ->toRawSql()
                ->toString(),
        );
    }

    public function test_select_query_to_raw_sql_with_like_clause(): void
    {
        $this->assertSameWithoutBackticks(
            expected: "SELECT * FROM books WHERE books.title LIKE '%fantasy%'",
            actual: query('books')
                ->select()
                ->whereLike('title', '%fantasy%')
                ->toRawSql()
                ->toString(),
        );
    }

    public function test_select_query_to_raw_sql_with_order_and_limit(): void
    {
        $rawSql = query('books')
            ->select()
            ->where('published', true)
            ->orderByRaw('created_at DESC')
            ->limit(10)
            ->offset(5)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'ORDER BY created_at DESC', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'LIMIT 10', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'OFFSET 5', haystack: $rawSql);
    }

    public function test_insert_query_to_raw_sql(): void
    {
        $rawSql = query('books')
            ->insert(['title' => 'New Book', 'author_id' => 1, 'published' => true])
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'INSERT INTO', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'books', haystack: $rawSql);
        $this->assertStringContainsString(needle: "'New Book'", haystack: $rawSql);
    }

    public function test_update_query_to_raw_sql(): void
    {
        $rawSql = query('books')
            ->update(title: 'Updated Title')
            ->where('id', 1)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'UPDATE', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'books', haystack: $rawSql);
        $this->assertStringContainsString(needle: "'Updated Title'", haystack: $rawSql);
        $this->assertStringContainsString(needle: 'WHERE', haystack: $rawSql);
    }

    public function test_delete_query_to_raw_sql(): void
    {
        $rawSql = query('books')
            ->delete()
            ->where('published', false)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'DELETE FROM', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'books', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'WHERE', haystack: $rawSql);
    }

    public function test_count_query_to_raw_sql(): void
    {
        $rawSql = query('books')
            ->count()
            ->where('published', true)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'SELECT COUNT(*)', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'FROM', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'books', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'WHERE', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'published', haystack: $rawSql);
    }

    public function test_count_query_with_column_to_raw_sql(): void
    {
        $rawSql = query('books')
            ->count('author_id')
            ->where('published', true)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'SELECT COUNT(', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'author_id', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'FROM', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'books', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'WHERE', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'published', haystack: $rawSql);
    }

    public function test_count_distinct_query_to_raw_sql(): void
    {
        $rawSql = query('books')
            ->count('author_id')
            ->distinct()
            ->where('published', true)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'SELECT COUNT(DISTINCT', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'author_id', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'FROM', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'books', haystack: $rawSql);
    }

    public function test_complex_select_query_with_groups_to_raw_sql(): void
    {
        $rawSql = query('books')
            ->select()
            ->where('published', true)
            ->whereGroup(function ($group): void {
                $group
                    ->where('category', 'fiction')
                    ->orWhere('rating', 4.5, WhereOperator::GREATER_THAN_OR_EQUAL);
            })
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'WHERE', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'published', haystack: $rawSql);
        $this->assertStringContainsString(needle: '(', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'OR', haystack: $rawSql);
        $this->assertStringContainsString(needle: ')', haystack: $rawSql);
    }

    public function test_raw_sql_with_string_escaping(): void
    {
        $rawSql = query('books')
            ->select()
            ->where('title', "Book with 'quotes'")
            ->where('description', 'Text with "double quotes"')
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: "'Book with ''quotes'''", haystack: $rawSql);
        $this->assertStringContainsString(needle: '"double quotes"', haystack: $rawSql);
    }

    public function test_raw_sql_with_model_queries(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::new(name: 'Test Author', type: AuthorType::A);
        $author->save();

        $rawSql = Author::select()
            ->where('name', 'Test Author')
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'SELECT', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'FROM', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'authors', haystack: $rawSql);
        $this->assertStringContainsString(needle: "'Test Author'", haystack: $rawSql);
    }

    public function test_raw_sql_preserves_field_aliases(): void
    {
        $rawSql = query('books')
            ->select('title as book_title', 'author_id as author')
            ->where('published', true)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'AS', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'book_title', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'author', haystack: $rawSql);
    }

    public function test_raw_sql_with_joins(): void
    {
        $rawSql = query('books')
            ->select('books.title', 'authors.name')
            ->join('LEFT JOIN authors ON authors.id = books.author_id')
            ->where('published', true)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'LEFT JOIN authors', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'ON authors.id = books.author_id', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'WHERE', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'published', haystack: $rawSql);
    }

    public function test_raw_sql_with_group_by_and_having(): void
    {
        $rawSql = query('books')
            ->select('author_id', 'COUNT(*) as book_count')
            ->where('published', true)
            ->groupBy('author_id')
            ->having('COUNT(*) > ?', 1)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'GROUP BY author_id', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'HAVING COUNT(*) > 1', haystack: $rawSql);
    }

    public function test_raw_sql_with_raw_subquery_in_where(): void
    {
        $rawSql = query('books')
            ->select()
            ->where('author_id IN (SELECT id FROM authors WHERE type = ?)', 'a')
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'WHERE author_id IN (', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'SELECT id FROM authors', haystack: $rawSql);
        $this->assertStringContainsString(needle: "type = 'a'", haystack: $rawSql);
        $this->assertStringContainsString(needle: ')', haystack: $rawSql);
    }

    public function test_raw_sql_handles_array_values_properly(): void
    {
        $rawSql = query('books')
            ->select()
            ->whereIn('category', ['fiction', 'mystery', 'thriller'])
            ->whereNotIn('status', ['draft', 'archived'])
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: "('fiction','mystery','thriller')", haystack: $rawSql);
        $this->assertStringContainsString(needle: "('draft','archived')", haystack: $rawSql);
    }

    public function test_raw_sql_with_enum_values(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        $rawSql = query('authors')
            ->select()
            ->where('type', AuthorType::A->value)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: "'a'", haystack: $rawSql);
    }

    public function test_raw_sql_consistency_across_database_dialects(): void
    {
        $rawSql = query('books')
            ->select('title', 'author_id')
            ->where('published', true)
            ->where('rating', 4.5, WhereOperator::GREATER_THAN_OR_EQUAL)
            ->orderByRaw('created_at DESC')
            ->limit(5)
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'SELECT', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'title', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'author_id', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'FROM', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'books', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'WHERE', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'published', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'rating', haystack: $rawSql);
        $this->assertStringContainsString(needle: '4.5', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'ORDER BY created_at DESC', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'LIMIT 5', haystack: $rawSql);
    }

    public function test_raw_sql_handles_null_and_empty_values(): void
    {
        $rawSql = query('books')
            ->select()
            ->whereNull('deleted_at')
            ->where('title', '')
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: 'deleted_at', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'IS NULL', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'title', haystack: $rawSql);
    }

    public function test_raw_sql_with_named_bindings(): void
    {
        $query = new Query(
            'SELECT * FROM books WHERE title = :title AND author_id = :author_id',
            ['title' => 'The Hobbit', 'author_id' => 1],
        );

        $rawSql = $query->toRawSql()->toString();

        $this->assertStringContainsString(needle: "'The Hobbit'", haystack: $rawSql);
        $this->assertStringContainsString(needle: '1', haystack: $rawSql);
        $this->assertStringNotContainsString(needle: ':title', haystack: $rawSql);
        $this->assertStringNotContainsString(needle: ':author_id', haystack: $rawSql);
    }

    public function test_raw_sql_with_positional_bindings(): void
    {
        $query = new Query(
            'SELECT * FROM books WHERE title = ? AND author_id = ? AND rating > ?',
            ['The Hobbit', 1, 4.5],
        );

        $rawSql = $query->toRawSql()->toString();

        $this->assertStringContainsString(needle: "'The Hobbit'", haystack: $rawSql);
        $this->assertStringContainsString(needle: '1', haystack: $rawSql);
        $this->assertStringContainsString(needle: '4.5', haystack: $rawSql);
        $this->assertStringNotContainsString(needle: '?', haystack: $rawSql);
    }

    public function test_raw_sql_with_mixed_data_types(): void
    {
        $rawSql = query('books')
            ->select()
            ->where('title', 'Mixed Types Test')
            ->where('id', 42)
            ->where('price', 19.99)
            ->where('published', true)
            ->whereNull('deleted_at')
            ->toRawSql()
            ->toString();

        $this->assertStringContainsString(needle: "'Mixed Types Test'", haystack: $rawSql);
        $this->assertStringContainsString(needle: '42', haystack: $rawSql);
        $this->assertStringContainsString(needle: '19.99', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'deleted_at', haystack: $rawSql);
        $this->assertStringContainsString(needle: 'IS NULL', haystack: $rawSql);
    }
}
