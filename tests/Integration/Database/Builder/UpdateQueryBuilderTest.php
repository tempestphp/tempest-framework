<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
use Tempest\Database\Exceptions\CannotInsertHasManyRelation;
use Tempest\Database\Exceptions\CannotUpdateHasManyRelation;
use Tempest\Database\Exceptions\CannotUpdateHasOneRelation;
use Tempest\Database\Exceptions\InvalidUpdateStatement;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
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
            ->where('`id` = ?', 10)
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `chapters`
            SET `title` = ?, `index` = ?
            WHERE `id` = ?
            SQL,
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

        $this->assertSame(
            <<<SQL
            UPDATE `chapters`
            SET `index` = ?
            SQL,
            $query->toSql(),
        );

        $this->assertSame(
            [0],
            $query->bindings,
        );
    }

    public function test_global_update_fails_without_allow_all(): void
    {
        $this->expectException(InvalidUpdateStatement::class);

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
            ->where('`id` = ?', 10)
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `books`
            SET `title` = ?
            WHERE `id` = ?
            SQL,
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
            id: new Id(10),
            title: 'Chapter 01',
        );

        $query = query($book)
            ->update(
                title: 'Chapter 02',
            )
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `books`
            SET `title` = ?
            WHERE `id` = ?
            SQL,
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
            id: new Id(10),
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
            id: new Id(10),
        );

        $bookQuery = query($book)
            ->update(author: Author::new(name: 'Brent'))
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `books`
            SET `author_id` = ?
            WHERE `id` = ?
            SQL,
            $bookQuery->toSql(),
        );

        $this->assertInstanceOf(Query::class, $bookQuery->bindings[0]);

        $authorQuery = $bookQuery->bindings[0];

        $this->assertSame(
            <<<SQL
            INSERT INTO `authors` (`name`)
            VALUES (?)
            SQL,
            $authorQuery->toSql(),
        );

        $this->assertSame(['Brent'], $authorQuery->bindings);
    }

    public function test_attach_existing_relation_on_update(): void
    {
        $book = Book::new(
            id: new Id(10),
        );

        $bookQuery = query($book)
            ->update(author: Author::new(id: new Id(5), name: 'Brent'))
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `books`
            SET `author_id` = ?
            WHERE `id` = ?
            SQL,
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
        } catch (CannotUpdateHasManyRelation $cannotUpdateHasManyRelation) {
            $this->assertStringContainsString(Book::class . '::$chapters', $cannotUpdateHasManyRelation->getMessage());
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
        } catch (CannotUpdateHasOneRelation $cannotUpdateHasOneRelation) {
            $this->assertStringContainsString(Book::class . '::$isbn', $cannotUpdateHasOneRelation->getMessage());
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
                fn (UpdateQueryBuilder $query) => $query->where('`id` = ?', 10),
            )
            ->when(
                false,
                fn (UpdateQueryBuilder $query) => $query->where('`id` = ?', 20),
            )
            ->build();

        $this->assertSame(
            <<<SQL
            UPDATE `chapters`
            SET `title` = ?, `index` = ?
            WHERE `id` = ?
            SQL,
            $query->toSql(),
        );

        $this->assertSame(
            ['Chapter 01', 1, 10],
            $query->bindings,
        );
    }

    public function test_update_with_non_object_model(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateAuthorTable::class);

        query('authors')->insert(
            ['id' => 1, 'name' => 'Brent'],
            ['id' => 2, 'name' => 'Other'],
        )->execute();

        query('authors')->update(
            name: 'Brendt',
        )->where('id = ?', 1)->execute();

        $count = query('authors')->count()->where('name = ?', 'Brendt')->execute();

        $this->assertSame(1, $count);
    }
}
