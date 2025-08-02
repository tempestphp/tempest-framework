<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\DeleteQueryBuilder;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class DeleteQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_delete_on_plain_table(): void
    {
        $query = query('foo')
            ->delete()
            ->where('`bar` = ?', 'boo')
            ->build();

        $this->assertSameWithoutBackticks(
            <<<SQL
            DELETE FROM `foo`
            WHERE `bar` = ?
            SQL,
            $query->toSql(),
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
            <<<SQL
            DELETE FROM `authors`
            SQL,
            $query->toSql(),
        );
    }

    public function test_delete_on_model_object(): void
    {
        $author = new Author(name: 'brent');
        $author->id = new Id(10);

        $query = query($author)
            ->delete()
            ->build();

        $this->assertSameWithoutBackticks(
            <<<SQL
            DELETE FROM `authors`
            WHERE `authors`.`id` = ?
            SQL,
            $query->toSql(),
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
                fn (DeleteQueryBuilder $query) => $query->where('`bar` = ?', 'boo'),
            )
            ->when(
                false,
                fn (DeleteQueryBuilder $query) => $query->where('`bar` = ?', 'foo'),
            )
            ->build();

        $this->assertSameWithoutBackticks(
            <<<SQL
            DELETE FROM `foo`
            WHERE `bar` = ?
            SQL,
            $query->toSql(),
        );

        $this->assertSame(
            'boo',
            $query->bindings[0],
        );
    }

    public function test_delete_with_non_object_model(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')->insert(
            ['id' => 1, 'name' => 'Brent'],
            ['id' => 2, 'name' => 'Other'],
        )->execute();

        query('authors')->delete()->where('id = ?', 1)->execute();

        $count = query('authors')->count()->where('id = ?', 1)->execute();

        $this->assertSame(0, $count);
    }

    public function test_multiple_where(): void
    {
        $sql = query('books')
            ->delete()
            ->where('title = ?', 'a')
            ->where('author_id = ?', 1)
            ->where('OR author_id = ?', 2)
            ->where('AND author_id <> NULL')
            ->toSql();

        $expected = <<<SQL
        DELETE FROM `books`
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
            ->delete()
            ->whereField('title', 'a')
            ->whereField('author_id', 1)
            ->toSql();

        $expected = <<<SQL
        DELETE FROM `books`
        WHERE books.title = ?
        AND books.author_id = ?
        SQL;

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_tap(): void
    {
        $query = query('foo')
            ->delete()
            ->tap(fn (DeleteQueryBuilder $query) => $query->where('bar = ?', 'boo'))
            ->build();

        $this->assertSameWithoutBackticks(<<<SQL
        DELETE FROM `foo`
        WHERE bar = ?
        SQL, $query->toSql());

        $this->assertSameWithoutBackticks('boo', $query->bindings[0]);
    }
}
