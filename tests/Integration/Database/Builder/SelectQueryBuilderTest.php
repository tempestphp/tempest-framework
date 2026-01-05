<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Direction;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreateIsbnTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Models\AWithEager;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class SelectQueryBuilderTest extends FrameworkIntegrationTestCase
{
    public function test_select_query(): void
    {
        $query = query('chapters')
            ->select('title', 'index')
            ->whereRaw('`title` = ?', 'Timeline Taxi')
            ->andWhereRaw('`index` <> ?', '1')
            ->orWhereRaw('`createdAt` > ?', '2025-01-01')
            ->orderByRaw('`index` ASC')
            ->build();

        $expected = 'SELECT title, index FROM chapters WHERE title = ? AND index <> ? OR createdAt > ? ORDER BY index ASC';

        $sql = $query->compile();
        $bindings = $query->bindings;

        $this->assertSameWithoutBackticks($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_select_multiple_fields_as_one(): void
    {
        $this->assertSameWithoutBackticks(
            'SELECT title AS t, index AS i FROM chapters',
            query('chapters')
                ->select('title AS t, index AS i')
                ->build()
                ->compile(),
        );

        $this->assertSameWithoutBackticks(
            'SELECT title, index FROM chapters',
            query('chapters')
                ->select('title, index')
                ->build()
                ->compile(),
        );
    }

    public function test_select_without_any_fields_specified(): void
    {
        $query = query('chapters')->select()->build();

        $sql = $query->compile();

        $expected = 'SELECT * FROM `chapters`';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_select_from_model(): void
    {
        $query = query(Author::class)->select()->build();

        $sql = $query->compile();

        $expected = 'SELECT authors.id AS `authors.id`, authors.name AS `authors.name`, authors.type AS `authors.type`, authors.publisher_id AS `authors.publisher_id` FROM `authors`';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_multiple_where(): void
    {
        $sql = query('books')
            ->select()
            ->whereRaw('title = ?', 'a')
            ->whereRaw('author_id = ?', 1)
            ->whereRaw('OR author_id = ?', 2)
            ->whereRaw('AND author_id <> NULL')
            ->compile();

        $expected = 'SELECT * FROM `books` WHERE title = ? AND author_id = ? OR author_id = ? AND author_id <> NULL';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_multiple_where_field(): void
    {
        $sql = query('books')
            ->select()
            ->where('title', 'a')
            ->where('author_id', 1)
            ->compile();

        $expected = 'SELECT * FROM `books` WHERE books.title = ? AND books.author_id = ?';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_where_statement(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $book = Book::select()->whereRaw('title = ?', 'B')->first();

        $this->assertSame('B', $book->title);
    }

    public function test_join(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::new(name: 'Brent')->save();
        Book::new(title: 'A', author: $author)->save();

        $query = query('books')->select('books.title AS book_title', 'authors.name')->join('authors on authors.id = books.author_id');

        $this->assertSame(
            [
                'book_title' => 'A',
                'name' => 'Brent',
            ],
            $query->first(),
        );
    }

    public function test_order_by(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $book = Book::select()->orderByRaw('title DESC')->first();

        $this->assertSame('D', $book->title);
    }

    public function test_order_by_with_field_and_direction(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $book = Book::select()->orderBy('title', Direction::DESC)->first();
        $this->assertSame('D', $book->title);

        $book = Book::select()->orderBy('title', Direction::ASC)->first();
        $this->assertSame('A', $book->title);
    }

    public function test_order_by_with_field_defaults_to_asc(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $book = Book::select()->orderBy('title')->first();
        $this->assertSame('A', $book->title);
    }

    public function test_order_by_raw_shorthand(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $book = Book::select()->orderBy('title DESC')->first();
        $this->assertSame('D', $book->title);
    }

    public function test_order_by_sql_generation(): void
    {
        $this->assertSameWithoutBackticks(
            expected: 'SELECT * FROM `books` ORDER BY `title` ASC',
            actual: query('books')->select()->orderBy('title')->compile(),
        );

        $this->assertSameWithoutBackticks(
            expected: 'SELECT * FROM `books` ORDER BY `title` DESC',
            actual: query('books')->select()->orderBy('title', Direction::DESC)->compile(),
        );

        $this->assertSameWithoutBackticks(
            expected: 'SELECT * FROM `books` ORDER BY title DESC NULLS LAST',
            actual: query('books')->select()->orderByRaw('title DESC NULLS LAST')->compile(),
        );
    }

    public function test_limit(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $books = Book::select()->limit(2)->all();

        $this->assertCount(2, $books);
        $this->assertSame('A', $books[0]->title);
        $this->assertSame('B', $books[1]->title);
    }

    public function test_offset(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $books = Book::select()
            ->limit(2)
            ->offset(2)
            ->all();

        $this->assertCount(2, $books);
        $this->assertSame('C', $books[0]->title);
        $this->assertSame('D', $books[1]->title);
    }

    public function test_chunk(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $results = [];
        Book::select()->chunk(function (array $chunk) use (&$results): void {
            $results = [...$results, ...$chunk];
        }, 2);
        $this->assertCount(4, $results);

        $results = [];
        Book::select()->whereRaw("title <> 'A'")->chunk(function (array $chunk) use (&$results): void {
            $results = [...$results, ...$chunk];
        }, 2);
        $this->assertCount(3, $results);
    }

    public function test_chunk_with_relation(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::new(name: 'Brent')->save();

        Book::new(title: 'A', author: $author)->save();
        Book::new(title: 'B', author: $author)->save();

        $results = [];
        Book::select()->with('author')->chunk(function (array $chunk) use (&$results): void {
            $results = [...$results, ...$chunk];
        }, 1);
        $this->assertCount(2, $results);
    }

    public function test_raw(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        $books = Book::select()->raw('LIMIT 1')->all();

        $this->assertCount(1, $books);
        $this->assertSame('A', $books[0]->title);
    }

    public function test_select_query_with_conditions(): void
    {
        $query = query('chapters')
            ->select('title', 'index')
            ->when(
                true,
                fn (SelectQueryBuilder $query) => $query
                    ->whereRaw('`title` = ?', 'Timeline Taxi')
                    ->andWhereRaw('`index` <> ?', '1')
                    ->orWhereRaw('`createdAt` > ?', '2025-01-01'),
            )
            ->when(
                false,
                fn (SelectQueryBuilder $query) => $query
                    ->whereRaw('`title` = ?', 'Timeline Uber')
                    ->andWhereRaw('`index` <> ?', '2')
                    ->orWhereRaw('`createdAt` > ?', '2025-01-02'),
            )
            ->orderByRaw('`index` ASC')
            ->build();

        $expected = 'SELECT title, index FROM `chapters` WHERE `title` = ? AND `index` <> ? OR `createdAt` > ? ORDER BY `index` ASC';

        $sql = $query->compile();
        $bindings = $query->bindings;

        $this->assertSameWithoutBackticks($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_select_first_with_non_object_model(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')
            ->insert(
                ['id' => 1, 'name' => 'Brent'],
                ['id' => 2, 'name' => 'Other'],
            )
            ->execute();

        $author = query('authors')
            ->select()
            ->where('id', 2)
            ->first();

        $this->assertSame(['id' => 2, 'name' => 'Other', 'type' => null, 'publisher_id' => null], $author);
    }

    public function test_select_all_with_non_object_model(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')
            ->insert(
                ['id' => 1, 'name' => 'Brent', 'type' => AuthorType::B],
                ['id' => 2, 'name' => 'Other', 'type' => null],
                ['id' => 3, 'name' => 'Another', 'type' => AuthorType::A],
            )
            ->execute();

        $authors = query('authors')
            ->select()
            ->whereRaw('name <> ?', 'Brent')
            ->all();

        $this->assertSame(
            [['id' => 2, 'name' => 'Other', 'type' => null, 'publisher_id' => null], ['id' => 3, 'name' => 'Another', 'type' => 'a', 'publisher_id' => null]],
            $authors,
        );
    }

    public function test_select_includes_belongs_to(): void
    {
        $query = query(Book::class)->select();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS `books.id`, books.title AS `books.title`, books.author_id AS `books.author_id` FROM `books`',
            $query->build()->compile(),
        );
    }

    public function test_with_belongs_to_relation(): void
    {
        $query = query(Book::class)
            ->select()
            ->with('author', 'chapters', 'isbn')
            ->build();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS `books.id`, books.title AS `books.title`, books.author_id AS `books.author_id`, authors.id AS `author.id`, authors.name AS `author.name`, authors.type AS `author.type`, authors.publisher_id AS `author.publisher_id`, chapters.id AS `chapters.id`, chapters.title AS `chapters.title`, chapters.contents AS `chapters.contents`, chapters.book_id AS `chapters.book_id`, isbns.id AS `isbn.id`, isbns.value AS `isbn.value`, isbns.book_id AS `isbn.book_id` FROM `books` LEFT JOIN authors ON authors.id = books.author_id LEFT JOIN chapters ON chapters.book_id = books.id LEFT JOIN isbns ON isbns.book_id = books.id',
            $query->compile(),
        );
    }

    public function test_select_query_execute_with_relations(): void
    {
        $this->seed();

        $books = query(Book::class)
            ->select()
            ->with('author', 'chapters', 'isbn')
            ->all();

        $this->assertCount(4, $books);
        $this->assertSame('LOTR 1', $books[0]->title);
        $this->assertSame('LOTR 2', $books[1]->title);
        $this->assertSame('LOTR 3', $books[2]->title);
        $this->assertSame('Timeline Taxi', $books[3]->title);

        $book = $books[0];
        $this->assertSame('Tolkien', $book->author->name);
        $this->assertCount(3, $book->chapters);

        $this->assertSame('LOTR 1.1', $book->chapters[0]->title);
        $this->assertSame('LOTR 1.2', $book->chapters[1]->title);
        $this->assertSame('LOTR 1.3', $book->chapters[2]->title);

        $this->assertSame('lotr-1', $book->isbn->value);
    }

    public function test_eager_loads_combined_with_manual_loads(): void
    {
        $query = AWithEager::select()->with('b.c')->compile();

        $this->assertSameWithoutBackticks(
            'SELECT a.id AS `a.id`, a.b_id AS `a.b_id`, b.id AS `b.id`, b.c_id AS `b.c_id`, c.id AS `b.c.id`, c.name AS `b.c.name` FROM `a` LEFT JOIN b ON b.id = a.b_id LEFT JOIN c ON c.id = b.c_id',
            $query,
        );
    }

    public function test_group_by(): void
    {
        $sql = query('authors')
            ->select()
            ->groupBy('name')
            ->compile();

        $expected = 'SELECT * FROM authors GROUP BY name';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_having(): void
    {
        $sql = query('authors')
            ->select()
            ->having('name = ?', 'Brent')
            ->compile();

        $expected = 'SELECT * FROM authors HAVING name = ?';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    public function test_paginate(): void
    {
        $this->seed();

        $page1 = query(Chapter::class)
            ->select()
            ->paginate(itemsPerPage: 2);

        $this->assertSame(1, $page1->currentPage);
        $this->assertSame(7, $page1->totalPages);
        $this->assertSame(13, $page1->totalItems);
        $this->assertSame(2, $page1->itemsPerPage);
        $this->assertSame(0, $page1->offset);
        $this->assertSame(2, $page1->limit);
        $this->assertSame(2, $page1->nextPage);
        $this->assertSame(null, $page1->previousPage);
        $this->assertSame(true, $page1->hasNext);
        $this->assertSame(false, $page1->hasPrevious);

        $this->assertSame('LOTR 1.1', $page1->data[0]->title);
        $this->assertSame('LOTR 1.2', $page1->data[1]->title);

        $page3 = query(Chapter::class)
            ->select()
            ->paginate(itemsPerPage: 2, currentPage: 3);

        $this->assertSame(3, $page3->currentPage);
        $this->assertSame('LOTR 2.2', $page3->data[0]->title);
        $this->assertSame('LOTR 2.3', $page3->data[1]->title);

        $page7 = query(Chapter::class)
            ->select()
            ->paginate(itemsPerPage: 2, currentPage: 7);

        $this->assertSame(7, $page7->currentPage);
        $this->assertSame('Timeline Taxi Chapter 4', $page7->data[0]->title);

        // capped to last page, so this will be page 7
        $page10 = query(Chapter::class)
            ->select()
            ->paginate(itemsPerPage: 2, currentPage: 10);

        $this->assertSame(7, $page10->currentPage);
        $this->assertSame('Timeline Taxi Chapter 4', $page10->data[0]->title);
    }

    public function test_paginate_with_where_condition(): void
    {
        $this->seed();

        $page1 = query(Chapter::class)
            ->select()
            ->whereField('book_id', value: 1)
            ->paginate(itemsPerPage: 2);

        $this->assertSame(3, $page1->totalItems);
        $this->assertSame(2, $page1->totalPages);
        $this->assertSame(1, $page1->currentPage);
        $this->assertCount(2, $page1->data);
        $this->assertSame('LOTR 1.1', $page1->data[0]->title);
        $this->assertSame('LOTR 1.2', $page1->data[1]->title);
    }

    public function test_select_query_builder_from_another_query_builder(): void
    {
        $this->seed();

        $source = query(Chapter::class)
            ->select()
            ->where('book_id', 1);

        $query = SelectQueryBuilder::fromQueryBuilder($source);
        $results = $query->all();

        $this->assertCount(3, $results);
        $this->assertSame('LOTR 1.1', $results[0]->title);
        $this->assertSame('LOTR 1.2', $results[1]->title);
        $this->assertSame('LOTR 1.3', $results[2]->title);
    }

    public function test_paginate_preserves_relations(): void
    {
        $this->seed();

        $page1 = query(Chapter::class)
            ->select()
            ->with('book')
            ->whereRaw('books.title = ?', 'LOTR 1')
            ->paginate(itemsPerPage: 5, currentPage: 1);

        $this->assertSame(3, $page1->totalItems);
        $this->assertCount(3, $page1->data);
        $this->assertSame('LOTR 1', $page1->data[0]->book->title);
    }

    public function test_paginate_with_nested_relations(): void
    {
        $this->seed();

        $page1 = Chapter::select()
            ->with('book.author')
            ->paginate(itemsPerPage: 5, currentPage: 1);

        $this->assertSame(13, $page1->totalItems);
        $this->assertCount(5, $page1->data);

        $this->assertInstanceOf(Author::class, $page1->data[0]->book->author);
        $this->assertSame('Tolkien', $page1->data[0]->book->author->name);
    }

    private function seed(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        query('authors')
            ->insert(
                ['name' => 'Brent'],
                ['name' => 'Tolkien'],
            )
            ->execute();

        query('books')
            ->insert(
                ['title' => 'LOTR 1', 'author_id' => 2],
                ['title' => 'LOTR 2', 'author_id' => 2],
                ['title' => 'LOTR 3', 'author_id' => 2],
                ['title' => 'Timeline Taxi', 'author_id' => 1],
            )
            ->execute();

        query('isbns')
            ->insert(
                ['value' => 'lotr-1', 'book_id' => 1],
                ['value' => 'lotr-2', 'book_id' => 2],
                ['value' => 'lotr-3', 'book_id' => 3],
                ['value' => 'tt', 'book_id' => 4],
            )
            ->execute();

        query('chapters')
            ->insert(
                ['title' => 'LOTR 1.1', 'book_id' => 1],
                ['title' => 'LOTR 1.2', 'book_id' => 1],
                ['title' => 'LOTR 1.3', 'book_id' => 1],
                ['title' => 'LOTR 2.1', 'book_id' => 2],
                ['title' => 'LOTR 2.2', 'book_id' => 2],
                ['title' => 'LOTR 2.3', 'book_id' => 2],
                ['title' => 'LOTR 3.1', 'book_id' => 3],
                ['title' => 'LOTR 3.2', 'book_id' => 3],
                ['title' => 'LOTR 3.3', 'book_id' => 3],
                ['title' => 'Timeline Taxi Chapter 1', 'book_id' => 4],
                ['title' => 'Timeline Taxi Chapter 2', 'book_id' => 4],
                ['title' => 'Timeline Taxi Chapter 3', 'book_id' => 4],
                ['title' => 'Timeline Taxi Chapter 4', 'book_id' => 4],
            )
            ->execute();
    }
}
