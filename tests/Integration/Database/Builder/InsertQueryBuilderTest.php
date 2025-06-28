<?php

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\Exceptions\HasManyRelationCouldNotBeInsterted;
use Tempest\Database\Exceptions\HasOneRelationCouldNotBeInserted;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class InsertQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_insert_on_plain_table(): void
    {
        $query = query('chapters')
            ->insert(
                title: 'Chapter 01',
                index: 1,
            )
            ->build();

        $expected = $this->buildExpectedInsert(<<<SQL
        INSERT INTO `chapters` (`title`, `index`)
        VALUES (?, ?)
        SQL);

        $this->assertSameWithoutBackticks(
            $expected,
            $query->toSql(),
        );

        $this->assertSame(
            ['Chapter 01', 1],
            $query->bindings,
        );
    }

    public function test_insert_with_batch(): void
    {
        $arrayOfStuff = [
            ['chapter' => 'Chapter 01', 'index' => 1],
            ['chapter' => 'Chapter 02', 'index' => 2],
            ['chapter' => 'Chapter 03', 'index' => 3],
        ];

        $query = query('chapters')
            ->insert(...$arrayOfStuff)
            ->build();

        $expected = $this->buildExpectedInsert(<<<SQL
        INSERT INTO `chapters` (`chapter`, `index`)
        VALUES (?, ?), (?, ?), (?, ?)
        SQL);

        $this->assertSameWithoutBackticks(
            $expected,
            $query->toSql(),
        );

        $this->assertSame(
            ['Chapter 01', 1, 'Chapter 02', 2, 'Chapter 03', 3],
            $query->bindings,
        );
    }

    public function test_insert_on_model_table(): void
    {
        $author = new Author(
            name: 'brent',
            type: AuthorType::A,
        );

        $query = query(Author::class)
            ->insert(
                $author,
                ['name' => 'other name', 'type' => AuthorType::B->value, 'publisher_id' => null],
            )
            ->build();

        $expected = $this->buildExpectedInsert(<<<SQL
        INSERT INTO `authors` (`name`, `type`, `publisher_id`)
        VALUES (?, ?, ?), (?, ?, ?)
        SQL);

        $this->assertSameWithoutBackticks($expected, $query->toSql());
        $this->assertSame(['brent', 'a', null, 'other name', 'b', null], $query->bindings);
    }

    public function test_insert_on_model_table_with_new_relation(): void
    {
        $book = Book::new(
            title: 'Timeline Taxi',
            author: Author::new(
                name: 'Brent',
            ),
        );

        $bookQuery = query(Book::class)
            ->insert(
                $book,
            )
            ->build();

        $expectedBookQuery = $this->buildExpectedInsert(<<<SQL
        INSERT INTO `books` (`title`, `author_id`)
        VALUES (?, ?)
        SQL);

        $this->assertSameWithoutBackticks($expectedBookQuery, $bookQuery->toSql());
        $this->assertSame('Timeline Taxi', $bookQuery->bindings[0]);
        $this->assertInstanceOf(Query::class, $bookQuery->bindings[1]);

        $authorQuery = $bookQuery->bindings[1];

        $expectedAuthorQuery = $this->buildExpectedInsert(<<<SQL
        INSERT INTO `authors` (`name`)
        VALUES (?)
        SQL);

        $this->assertSameWithoutBackticks($expectedAuthorQuery, $authorQuery->toSql());
        $this->assertSame('Brent', $authorQuery->bindings[0]);
    }

    public function test_insert_on_model_table_with_existing_relation(): void
    {
        $book = Book::new(
            title: 'Timeline Taxi',
            author: Author::new(
                id: new Id(10),
                name: 'Brent',
            ),
        );

        $bookQuery = query(Book::class)
            ->insert(
                $book,
            )
            ->build();

        $expectedBookQuery = $this->buildExpectedInsert(<<<SQL
        INSERT INTO `books` (`title`, `author_id`)
        VALUES (?, ?)
        SQL);

        $this->assertSameWithoutBackticks($expectedBookQuery, $bookQuery->toSql());
        $this->assertSame('Timeline Taxi', $bookQuery->bindings[0]);
        $this->assertSame(10, $bookQuery->bindings[1]);
    }

    public function test_inserting_has_many_via_parent_model_throws_exception(): void
    {
        try {
            query(Book::class)
                ->insert(
                    title: 'Timeline Taxi',
                    chapters: ['title' => 'Chapter 01'],
                )
                ->build();
        } catch (HasManyRelationCouldNotBeInsterted $hasManyRelationCouldNotBeInsterted) {
            $this->assertStringContainsString(Book::class . '::$chapters', $hasManyRelationCouldNotBeInsterted->getMessage());
        }
    }

    public function test_inserting_has_one_via_parent_model_throws_exception(): void
    {
        try {
            query(Book::class)
                ->insert(
                    title: 'Timeline Taxi',
                    isbn: ['value' => '979-8344313764'],
                )
                ->build();
        } catch (HasOneRelationCouldNotBeInserted $hasOneRelationCouldNotBeInserted) {
            $this->assertStringContainsString(Book::class . '::$isbn', $hasOneRelationCouldNotBeInserted->getMessage());
        }
    }

    public function test_then_method(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $id = query(Book::class)
            ->insert(title: 'Timeline Taxi')
            ->then(
                fn (Id $id) => query(Chapter::class)->insert(
                    ['title' => 'Chapter 01', 'book_id' => $id],
                    ['title' => 'Chapter 02', 'book_id' => $id],
                ),
                fn (Id $id) => query(Chapter::class)->insert(
                    ['title' => 'Chapter 03', 'book_id' => $id],
                ),
            )
            ->execute();

        $book = Book::select()->with('chapters')->get($id);

        $this->assertCount(3, $book->chapters);
        $this->assertSame('Chapter 01', $book->chapters[0]->title);
        $this->assertSame('Chapter 02', $book->chapters[1]->title);
        $this->assertSame('Chapter 03', $book->chapters[2]->title);
    }

    public function test_insert_with_non_object_model(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')->insert(
            ['id' => 1, 'name' => 'Brent'],
            ['id' => 2, 'name' => 'Other'],
        )->execute();

        $count = query('authors')->count()->execute();

        $this->assertSame(2, $count);
    }

    private function buildExpectedInsert(string $query): string
    {
        if ($this->container->get(Database::class)->dialect === DatabaseDialect::POSTGRESQL) {
            $query .= ' RETURNING *';
        }

        return $query;
    }
}
