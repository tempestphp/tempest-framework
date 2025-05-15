<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreateIsbnTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
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
            ->where('`title` = ?', 'Timeline Taxi')
            ->andWhere('`index` <> ?', '1')
            ->orWhere('`createdAt` > ?', '2025-01-01')
            ->orderBy('`index` ASC')
            ->build();

        $expected = <<<SQL
        SELECT title, index
        FROM `chapters`
        WHERE `title` = ?
        AND `index` <> ?
        OR `createdAt` > ?
        ORDER BY `index` ASC
        SQL;

        $sql = $query->toSql();
        $bindings = $query->bindings;

        $this->assertSame($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_select_without_any_fields_specified(): void
    {
        $query = query('chapters')->select()->build();

        $sql = $query->toSql();

        $expected = <<<SQL
        SELECT *
        FROM `chapters`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_select_from_model(): void
    {
        $query = query(Author::class)->select()->build();

        $sql = $query->toSql();

        $expected = <<<SQL
        SELECT authors.name AS `authors.name`, authors.type AS `authors.type`, authors.id AS `authors.id`
        FROM `authors`
        SQL;

        $this->assertSame($expected, $sql);
    }

    public function test_where_statement(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(title: 'A')->save();
        Book::new(title: 'B')->save();
        Book::new(title: 'C')->save();
        Book::new(title: 'D')->save();

        $book = Book::select()->where('title = ?', 'B')->first();

        $this->assertSame('B', $book->title);
    }

    public function test_join(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
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
        $this->migrate(
            CreateMigrationsTable::class,
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

    public function test_limit(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
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
        $this->migrate(
            CreateMigrationsTable::class,
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
        $this->migrate(
            CreateMigrationsTable::class,
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
        Book::select()->where('title <> "A"')->chunk(function (array $chunk) use (&$results): void {
            $results = [...$results, ...$chunk];
        }, 2);
        $this->assertCount(3, $results);
    }

    public function test_raw(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
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
                    ->where('`title` = ?', 'Timeline Taxi')
                    ->andWhere('`index` <> ?', '1')
                    ->orWhere('`createdAt` > ?', '2025-01-01'),
            )
            ->when(
                false,
                fn (SelectQueryBuilder $query) => $query
                    ->where('`title` = ?', 'Timeline Uber')
                    ->andWhere('`index` <> ?', '2')
                    ->orWhere('`createdAt` > ?', '2025-01-02'),
            )
            ->orderBy('`index` ASC')
            ->build();

        $expected = <<<SQL
        SELECT title, index
        FROM `chapters`
        WHERE `title` = ?
        AND `index` <> ?
        OR `createdAt` > ?
        ORDER BY `index` ASC
        SQL;

        $sql = $query->toSql();
        $bindings = $query->bindings;

        $this->assertSame($expected, $sql);
        $this->assertSame(['Timeline Taxi', '1', '2025-01-01'], $bindings);
    }

    public function test_select_first_with_non_object_model(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateAuthorTable::class);

        query('authors')->insert(
            ['id' => 1, 'name' => 'Brent'],
            ['id' => 2, 'name' => 'Other'],
        )->execute();

        $author = query('authors')
            ->select()
            ->whereField('id', 2)
            ->first();

        $this->assertSame(['id' => 2, 'name' => 'Other', 'type' => null], $author);
    }

    public function test_select_all_with_non_object_model(): void
    {
        $this->migrate(CreateMigrationsTable::class, CreateAuthorTable::class);

        query('authors')->insert(
            ['id' => 1, 'name' => 'Brent', 'type' => AuthorType::B],
            ['id' => 2, 'name' => 'Other', 'type' => null],
            ['id' => 3, 'name' => 'Another', 'type' => AuthorType::A],
        )->execute();

        $authors = query('authors')
            ->select()
            ->where('name <> ?', 'Brent')
            ->all();

        $this->assertSame(
            [['id' => 2, 'name' => 'Other', 'type' => null], ['id' => 3, 'name' => 'Another', 'type' => 'a']],
            $authors,
        );
    }

    public function test_select_includes_belongs_to(): void
    {
        $query = query(Book::class)->select();

        $this->assertSame(<<<SQL
        SELECT books.title AS `books.title`, books.author_id AS `books.author_id`, books.id AS `books.id`
        FROM `books`
        SQL, $query->build()->toSql());
    }

    public function test_with_belongs_to_relation(): void
    {
        $query = query(Book::class)
            ->select()
            ->with('author', 'chapters', 'isbn')
            ->build();

        $this->assertSame(<<<SQL
        SELECT books.title AS `books.title`, books.author_id AS `books.author_id`, books.id AS `books.id`, authors.name AS `authors.name`, authors.type AS `authors.type`, authors.id AS `authors.id`, chapters.title AS `chapters.title`, chapters.contents AS `chapters.contents`, chapters.book_id AS `chapters.book_id`, chapters.id AS `chapters.id`, isbns.value AS `isbns.value`, isbns.book_id AS `isbns.book_id`
        FROM `books`
        LEFT JOIN authors ON authors.id = books.author_id
        LEFT JOIN chapters ON chapters.book_id = books.id
        LEFT JOIN isbns ON isbns.book_id = books.id
        SQL, $query->toSql());
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

    private function seed(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        query('authors')->insert(
            ['name' => 'Brent'],
            ['name' => 'Tolkien'],
        )->execute();

        query('books')->insert(
            ['title' => 'LOTR 1', 'author_id' => 2],
            ['title' => 'LOTR 2', 'author_id' => 2],
            ['title' => 'LOTR 3', 'author_id' => 2],
            ['title' => 'Timeline Taxi', 'author_id' => 1],
        )->execute();

        query('isbns')->insert(
            ['value' => 'lotr-1', 'book_id' => 1],
            ['value' => 'lotr-2', 'book_id' => 2],
            ['value' => 'lotr-3', 'book_id' => 3],
            ['value' => 'tt', 'book_id' => 4],
        )->execute();

        query('chapters')->insert(
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
        )->execute();
    }
}
