<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\DeleteQueryBuilder;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class DeleteQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_delete_on_plain_table(): void
    {
        $query = query('foo')
            ->delete()
            ->whereRaw('`bar` = ?', 'boo')
            ->build();

        $this->assertSameWithoutBackticks(
            'DELETE FROM `foo` WHERE `bar` = ?',
            $query->compile(),
        );

        $this->assertSameWithoutBackticks(
            'boo',
            $query->bindings[0],
        );
    }

    public function test_delete_on_model_table(): void
    {
        $query = query(Author::class)
            ->delete()
            ->allowAll()
            ->build();

        $this->assertSameWithoutBackticks(
            'DELETE FROM `authors`',
            $query->compile(),
        );
    }

    public function test_delete_on_model_object(): void
    {
        $author = new Author(name: 'brent');
        $author->id = new PrimaryKey(10);

        $query = query($author)
            ->delete()
            ->build();

        $this->assertSameWithoutBackticks(
            'DELETE FROM `authors` WHERE `authors`.`id` = ?',
            $query->compile(),
        );

        $this->assertSame(
            10,
            $query->bindings[0],
        );
    }

    public function test_delete_on_plain_table_with_conditions(): void
    {
        $query = query('foo')
            ->delete()
            ->when(
                true,
                fn (DeleteQueryBuilder $query) => $query->whereRaw('`bar` = ?', 'boo'),
            )
            ->when(
                false,
                fn (DeleteQueryBuilder $query) => $query->whereRaw('`bar` = ?', 'foo'),
            )
            ->build();

        $this->assertSameWithoutBackticks(
            'DELETE FROM `foo` WHERE `bar` = ?',
            $query->compile(),
        );

        $this->assertSame(
            'boo',
            $query->bindings[0],
        );
    }

    public function test_delete_with_non_object_model(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')
            ->insert(
                ['id' => 1, 'name' => 'Brent'],
                ['id' => 2, 'name' => 'Other'],
            )
            ->execute();

        query('authors')->delete()->whereRaw('id = ?', 1)->execute();

        $count = query('authors')->count()->whereRaw('id = ?', 1)->execute();

        $this->assertSame(0, $count);
    }

    public function test_multiple_where_raw(): void
    {
        $sql = query('books')
            ->delete()
            ->whereRaw('title = ?', 'a')
            ->whereRaw('author_id = ?', 1)
            ->whereRaw('OR author_id = ?', 2)
            ->whereRaw('AND author_id <> NULL')
            ->compile();

        $expected = 'DELETE FROM `books` WHERE title = ? AND author_id = ? OR author_id = ? AND author_id <> NULL';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_multiple_where(): void
    {
        $sql = query('books')
            ->delete()
            ->where('title', 'a')
            ->where('author_id', 1)
            ->compile();

        $expected = 'DELETE FROM `books` WHERE books.title = ? AND books.author_id = ?';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_nested_where_with_delete_query(): void
    {
        $query = query('books')
            ->delete()
            ->whereRaw('status = ?', 'draft')
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereRaw('created_at < ?', '2022-01-01')
                    ->andWhereRaw('author_id IS NULL');
            })
            ->build();

        $expected = 'DELETE FROM books WHERE status = ? AND (created_at < ? AND author_id IS NULL)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['draft', '2022-01-01'], $query->bindings);
    }

    public function test_delete_query_builder_from_another_query_builder(): void
    {
        $query = query(Chapter::class)
            ->select()
            ->where('book_id', 1);

        $this->assertCount(1, DeleteQueryBuilder::fromQueryBuilder($query)->wheres);
    }
}
