<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\Exceptions\HasManyRelationCouldNotBeUpdated;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeUpdated;
use Tempest\Database\Exceptions\UpdateStatementWasInvalid;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class UpdateQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_update_on_plain_table(): void
    {
        $query = query('chapters')
            ->update(
                title: 'Chapter 01',
                index: 1,
            )
            ->whereRaw('`id` = ?', 10)
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `chapters` SET `title` = ?, `index` = ? WHERE `id` = ?',
            $query->toSql(),
        );

        $this->assertSame(
            ['Chapter 01', 1, 10],
            $query->bindings,
        );
    }

    public function test_global_update(): void
    {
        $query = query('chapters')
            ->update(index: 0)
            ->allowAll()
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `chapters` SET `index` = ?',
            $query->toSql(),
        );

        $this->assertSame(
            [0],
            $query->bindings,
        );
    }

    public function test_global_update_fails_without_allow_all(): void
    {
        $this->expectException(UpdateStatementWasInvalid::class);

        query('chapters')
            ->update(index: 0)
            ->build()
            ->toSql();
    }

    public function test_model_update_with_values(): void
    {
        $query = query(Book::class)
            ->update(
                title: 'Chapter 02',
            )
            ->whereRaw('`id` = ?', 10)
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `books` SET `title` = ? WHERE `id` = ?',
            $query->toSql(),
        );

        $this->assertSame(
            ['Chapter 02', 10],
            $query->bindings,
        );
    }

    public function test_model_update_with_object(): void
    {
        $book = Book::new(
            id: new PrimaryKey(10),
            title: 'Chapter 01',
        );

        $query = query($book)
            ->update(
                title: 'Chapter 02',
            )
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `books` SET `title` = ? WHERE `books`.`id` = ?',
            $query->toSql(),
        );

        $this->assertSame(
            ['Chapter 02', 10],
            $query->bindings,
        );
    }

    public function test_model_values_get_serialized(): void
    {
        $author = Author::new(
            id: new PrimaryKey(10),
        );

        $query = query($author)
            ->update(
                type: AuthorType::A,
            )
            ->build();

        $this->assertSame(
            ['a', 10],
            $query->bindings,
        );
    }

    public function test_insert_new_relation_on_update(): void
    {
        $book = Book::new(
            id: new PrimaryKey(10),
        );

        $bookQuery = query($book)
            ->update(author: Author::new(name: 'Brent'))
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `books` SET `author_id` = ? WHERE `books`.`id` = ?',
            $bookQuery->toSql(),
        );

        $this->assertInstanceOf(Query::class, $bookQuery->bindings[0]);

        $authorQuery = $bookQuery->bindings[0];

        $expected = 'INSERT INTO `authors` (`name`) VALUES (?)';

        if ($this->container->get(Database::class)->dialect === DatabaseDialect::POSTGRESQL) {
            $expected .= ' RETURNING *';
        }

        $this->assertSameWithoutBackticks(
            $expected,
            $authorQuery->toSql(),
        );

        $this->assertSame(['Brent'], $authorQuery->bindings);
    }

    public function test_attach_existing_relation_on_update(): void
    {
        $book = Book::new(
            id: new PrimaryKey(10),
        );

        $bookQuery = query($book)
            ->update(author: Author::new(id: new PrimaryKey(5), name: 'Brent'))
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `books` SET `author_id` = ? WHERE `books`.`id` = ?',
            $bookQuery->toSql(),
        );

        $this->assertSame([5, 10], $bookQuery->bindings);
    }

    public function test_update_has_many_relation_via_parent_model_throws_exception(): void
    {
        try {
            query(Book::class)
                ->update(
                    title: 'Timeline Taxi',
                    chapters: ['title' => 'Chapter 01'],
                )
                ->build();
        } catch (HasManyRelationCouldNotBeUpdated $hasManyRelationCouldNotBeUpdated) {
            $this->assertStringContainsString(Book::class . '::$chapters', $hasManyRelationCouldNotBeUpdated->getMessage());
        }
    }

    public function test_update_has_one_relation_via_parent_model_throws_exception(): void
    {
        try {
            query(Book::class)
                ->update(
                    title: 'Timeline Taxi',
                    isbn: ['value' => '979-8344313764'],
                )
                ->build();
        } catch (HasOneRelationCouldNotBeUpdated $hasOneRelationCouldNotBeUpdated) {
            $this->assertStringContainsString(Book::class . '::$isbn', $hasOneRelationCouldNotBeUpdated->getMessage());
        }
    }

    public function test_update_on_plain_table_with_conditions(): void
    {
        $query = query('chapters')
            ->update(
                title: 'Chapter 01',
                index: 1,
            )
            ->when(
                true,
                fn (UpdateQueryBuilder $query) => $query->whereRaw('`id` = ?', 10),
            )
            ->when(
                false,
                fn (UpdateQueryBuilder $query) => $query->whereRaw('`id` = ?', 20),
            )
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `chapters` SET `title` = ?, `index` = ? WHERE `id` = ?',
            $query->toSql(),
        );

        $this->assertSame(
            ['Chapter 01', 1, 10],
            $query->bindings,
        );
    }

    public function test_update_with_non_object_model(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')->insert(
            ['id' => 1, 'name' => 'Brent'],
            ['id' => 2, 'name' => 'Other'],
        )->execute();

        query('authors')->update(
            name: 'Brendt',
        )->whereRaw('id = ?', 1)->execute();

        $count = query('authors')->count()->whereRaw('name = ?', 'Brendt')->execute();

        $this->assertSame(1, $count);
    }

    public function test_multiple_where(): void
    {
        $sql = query('books')
            ->update(
                title: 'Timeline Taxi',
            )
            ->whereRaw('title = ?', 'a')
            ->whereRaw('author_id = ?', 1)
            ->whereRaw('OR author_id = ?', 2)
            ->whereRaw('AND author_id <> NULL')
            ->toSql();

        $expected = 'UPDATE `books` SET title = ? WHERE title = ? AND author_id = ? OR author_id = ? AND author_id <> NULL';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_multiple_where_field(): void
    {
        $sql = query('books')
            ->update(
                title: 'Timeline Taxi',
            )
            ->where('title', 'a')
            ->where('author_id', 1)
            ->toSql();

        $expected = 'UPDATE `books` SET title = ? WHERE books.title = ? AND books.author_id = ?';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_nested_where_with_update_query(): void
    {
        $query = query('books')
            ->update(status: 'archived')
            ->whereRaw('published = ?', true)
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereRaw('views < ?', 100)
                    ->orWhereRaw('last_accessed < ?', '2023-01-01');
            })
            ->build();

        $expected = 'UPDATE books SET status = ? WHERE published = ? AND (views < ? OR last_accessed < ?)';

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['archived', true, 100, '2023-01-01'], $query->bindings);
    }

    public function test_update_mapping(): void
    {
        $author = Author::new(id: new PrimaryKey(1), name: 'original');

        $query = query($author)
            ->update(name: 'other')
            ->build();

        $dialect = $this->container->get(Database::class)->dialect;

        $expected = match ($dialect) {
            DatabaseDialect::POSTGRESQL => <<<'SQL'
            UPDATE authors SET name = ? WHERE authors.id = ?
            SQL,
            default => <<<'SQL'
            UPDATE `authors` SET `name` = ? WHERE `authors`.`id` = ?
            SQL,
        };

        $this->assertSame($expected, $query->toSql()->toString());

        $this->assertSame(['other', 1], $query->bindings);
    }
}
