<?php

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTagTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Migrations\CreateTagTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\BookTransfer;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Fixtures\Modules\Books\Models\Tag;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class InsertQueryBuilderTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function insert_on_plain_table(): void
    {
        $query = query('chapters')
            ->insert(title: 'Chapter 01', index: 1)
            ->build();

        $expected = $this->buildExpectedInsert('INSERT INTO `chapters` (`title`, `index`) VALUES (?, ?)');

        $this->assertSameWithoutBackticks(
            $expected,
            $query->compile(),
        );

        $this->assertSame(
            ['Chapter 01', 1],
            $query->bindings,
        );
    }

    #[Test]
    public function insert_with_batch(): void
    {
        $arrayOfStuff = [
            ['chapter' => 'Chapter 01', 'index' => 1],
            ['chapter' => 'Chapter 02', 'index' => 2],
            ['chapter' => 'Chapter 03', 'index' => 3],
        ];

        $query = query('chapters')
            ->insert(...$arrayOfStuff)
            ->build();

        $expected = $this->buildExpectedInsert('INSERT INTO `chapters` (`chapter`, `index`) VALUES (?, ?), (?, ?), (?, ?)');

        $this->assertSameWithoutBackticks(
            $expected,
            $query->compile(),
        );

        $this->assertSame(
            ['Chapter 01', 1, 'Chapter 02', 2, 'Chapter 03', 3],
            $query->bindings,
        );
    }

    #[Test]
    public function insert_on_model_table(): void
    {
        $author = new Author(name: 'brent', type: AuthorType::A);
        $query = query(Author::class)
            ->insert(
                $author,
                ['name' => 'other name', 'type' => AuthorType::B, 'publisher_id' => null],
            )
            ->build();

        $expected = $this->buildExpectedInsert('INSERT INTO `authors` (`name`, `type`, `publisher_id`) VALUES (?, ?, ?), (?, ?, ?)');

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['brent', 'a', null, 'other name', 'b', null], $query->bindings);
    }

    #[Test]
    public function insert_on_model_table_with_new_relation(): void
    {
        $book = Book::new(
            title: 'Timeline Taxi',
            author: Author::new(name: 'Brent'),
        );

        $bookQuery = query(Book::class)
            ->insert($book)
            ->build();

        $expectedBookQuery = $this->buildExpectedInsert('INSERT INTO `books` (`title`, `author_id`) VALUES (?, ?)');

        $this->assertSameWithoutBackticks($expectedBookQuery, $bookQuery->compile());
        $this->assertSame('Timeline Taxi', $bookQuery->bindings[0]);
        $this->assertInstanceOf(Query::class, $bookQuery->bindings[1]);

        $authorQuery = $bookQuery->bindings[1];

        $expectedAuthorQuery = $this->buildExpectedInsert('INSERT INTO `authors` (`name`) VALUES (?)');

        $this->assertSameWithoutBackticks($expectedAuthorQuery, $authorQuery->compile());
        $this->assertSame('Brent', $authorQuery->bindings[0]);
    }

    #[Test]
    public function insert_on_model_table_with_existing_relation(): void
    {
        $book = Book::new(
            title: 'Timeline Taxi',
            author: Author::new(
                id: new PrimaryKey(10),
                name: 'Brent',
            ),
        );

        $bookQuery = query(Book::class)
            ->insert($book)
            ->build();

        $expectedBookQuery = $this->buildExpectedInsert('INSERT INTO `books` (`title`, `author_id`) VALUES (?, ?)');

        $this->assertSameWithoutBackticks($expectedBookQuery, $bookQuery->compile());
        $this->assertSame('Timeline Taxi', $bookQuery->bindings[0]);
        $this->assertSame(10, $bookQuery->bindings[1]);
    }

    #[Test]
    public function then_method(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class, CreateBookTable::class, CreateChapterTable::class);

        $id = query(Book::class)
            ->insert(title: 'Timeline Taxi')
            ->then(
                fn (PrimaryKey $id) => query(Chapter::class)->insert(
                    ['title' => 'Chapter 01', 'book_id' => $id],
                    ['title' => 'Chapter 02', 'book_id' => $id],
                ),
                fn (PrimaryKey $id) => query(Chapter::class)->insert(
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

    #[Test]
    public function insert_with_non_object_model(): void
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

    private function buildExpectedInsert(string $query): string
    {
        if ($this->container->get(Database::class)->dialect === DatabaseDialect::POSTGRESQL) {
            $query .= ' RETURNING *';
        }

        return $query;
    }

    #[Test]
    public function insert_mapping(): void
    {
        $author = Author::new(name: 'test');

        $query = query(Author::class)
            ->insert($author)
            ->build();

        $dialect = $this->container->get(Database::class)->dialect;

        $expected = match ($dialect) {
            DatabaseDialect::POSTGRESQL => <<<'SQL'
            INSERT INTO "authors" ("name") VALUES (?) RETURNING *
            SQL,
            default => <<<'SQL'
            INSERT INTO `authors` (`name`) VALUES (?)
            SQL,
        };

        $this->assertSame($expected, $query->compile()->toString());
        $this->assertSame(['test'], $query->bindings);
    }

    #[Test]
    public function insert_with_belongs_to_many_creates_pivot_rows(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateTagTable::class,
            CreateBookTagTable::class,
        );

        $tag = Tag::new(
            label: 'php',
            books: [
                Book::new(title: 'Book One'),
                Book::new(title: 'Book Two'),
            ],
        );

        $tagId = query(model: Tag::class)
            ->insert($tag)
            ->execute();

        $this->assertNotNull($tagId);

        $bookCount = query(model: 'books')->count()->execute();
        $pivotCount = query(model: 'books_tags')->count()->execute();

        $this->assertSame(2, $bookCount);
        $this->assertSame(2, $pivotCount);
    }

    #[Test]
    public function insert_skips_has_many_through_property(): void
    {
        $tag = Tag::new(label: 'php');

        $query = query(model: Tag::class)
            ->insert($tag)
            ->build();

        $expected = $this->buildExpectedInsert(query: 'INSERT INTO `tags` (`label`) VALUES (?)');

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['php'], $query->bindings);
    }

    #[Test]
    public function insert_with_two_relations_to_same_model(): void
    {
        $transfer = BookTransfer::new(
            sender: Author::new(id: new PrimaryKey(1), name: 'Alice'),
            recipient: Author::new(id: new PrimaryKey(2), name: 'Bob'),
            reason: 'Collaboration',
        );

        $query = query(BookTransfer::class)
            ->insert($transfer)
            ->build();

        $expected = $this->buildExpectedInsert('INSERT INTO `book_transfers` (`sender_id`, `recipient_id`, `reason`) VALUES (?, ?, ?)');

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(1, $query->bindings[0]);
        $this->assertSame(2, $query->bindings[1]);
        $this->assertSame('Collaboration', $query->bindings[2]);
    }

    #[Test]
    public function insert_with_two_relations_to_same_model_one_null(): void
    {
        $transfer = BookTransfer::new(
            sender: Author::new(id: new PrimaryKey(1), name: 'Alice'),
            reason: 'Solo',
        );

        $query = query(BookTransfer::class)
            ->insert($transfer)
            ->build();

        $expected = $this->buildExpectedInsert('INSERT INTO `book_transfers` (`sender_id`, `recipient_id`, `reason`) VALUES (?, ?, ?)');

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(1, $query->bindings[0]);
        $this->assertNull($query->bindings[1]);
        $this->assertSame('Solo', $query->bindings[2]);
    }

    #[Test]
    public function insert_skips_has_one_through_property(): void
    {
        $tag = Tag::new(label: 'php');

        $query = query(model: Tag::class)
            ->insert($tag)
            ->build();

        $expected = $this->buildExpectedInsert(query: 'INSERT INTO `tags` (`label`) VALUES (?)');

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['php'], $query->bindings);
    }
}
