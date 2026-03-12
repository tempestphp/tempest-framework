<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class NestedWhereTest extends FrameworkIntegrationTestCase
{
    public function test_nested_where_with_and_group(): void
    {
        $query = query('books')
            ->select()
            ->whereRaw('title = ?', 'test')
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereRaw('author_id = ?', 1)
                    ->orWhereRaw('author_id = ?', 2);
            })
            ->build();

        $expected = 'SELECT * FROM books WHERE title = ? AND (author_id = ? OR author_id = ?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['test', 1, 2], $query->bindings);
    }

    public function test_nested_where_with_or_group(): void
    {
        $query = query('books')
            ->select()
            ->whereRaw('status = ?', 'active')
            ->orWhereGroup(function ($group): void {
                $group
                    ->whereRaw('priority = ?', 'high')
                    ->andWhereRaw('urgent = ?', true);
            })
            ->build();

        $expected = 'SELECT * FROM books WHERE status = ? OR (priority = ? AND urgent = ?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['active', 'high', true], $query->bindings);
    }

    public function test_deeply_nested_where_groups(): void
    {
        $query = query('books')
            ->select()
            ->whereRaw('published = ?', true)
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereRaw('category = ?', 'fiction')
                    ->orWhereGroup(function ($innerGroup): void {
                        $innerGroup
                            ->whereRaw('author_name = ?', 'Tolkien')
                            ->andWhereRaw('rating > ?', 4.5);
                    });
            })
            ->build();

        $expected = 'SELECT * FROM books WHERE published = ? AND (category = ? OR (author_name = ? AND rating > ?))';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 'fiction', 'Tolkien', 4.5], $query->bindings);
    }

    public function test_complex_nested_where_scenario(): void
    {
        // WHERE status = 'published'
        // AND (
        //   (category = 'fiction' AND rating > 4.0)
        //   OR
        //   (category = 'non-fiction' AND author_id IN (1, 2, 3))
        // )
        // AND created_at > '2024-01-01'

        $query = query('books')
            ->select()
            ->whereRaw('status = ?', 'published')
            ->andWhereGroup(function ($group): void {
                $group
                    ->andWhereGroup(function ($innerGroup): void {
                        $innerGroup
                            ->whereRaw('category = ?', 'fiction')
                            ->andWhereRaw('rating > ?', 4.0);
                    })
                    ->orWhereGroup(function ($innerGroup): void {
                        $innerGroup
                            ->whereRaw('category = ?', 'non-fiction')
                            ->andWhereRaw('author_id IN (?, ?, ?)', 1, 2, 3);
                    });
            })
            ->andWhereRaw('created_at > ?', '2024-01-01')
            ->build();

        $expected = 'SELECT * FROM books WHERE status = ? AND ((category = ? AND rating > ?) OR (category = ? AND author_id IN (?, ?, ?))) AND created_at > ?';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(
            expected: [
                'published',
                'fiction',
                4.0,
                'non-fiction',
                1,
                2,
                3,
                '2024-01-01',
            ],
            actual: $query->bindings,
        );
    }

    public function test_where_group_without_existing_conditions(): void
    {
        $query = query('books')
            ->select()
            ->whereGroup(function ($group): void {
                $group
                    ->whereRaw('title LIKE ?', '%test%')
                    ->orWhereRaw('description LIKE ?', '%test%');
            })
            ->build();

        $expected = 'SELECT * FROM books WHERE (title LIKE ? OR description LIKE ?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['%test%', '%test%'], $query->bindings);
    }

    public function test_nested_where_with_where(): void
    {
        $query = query('books')
            ->select()
            ->where('published', true)
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereRaw('category = ?', 'fiction')
                    ->orWhereRaw('priority = ?', 'high');
            })
            ->build();

        $expected = 'SELECT * FROM books WHERE books.published = ? AND (category = ? OR priority = ?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame([true, 'fiction', 'high'], $query->bindings);
    }
}
