<?php

namespace Tests\Tempest\Integration\Testing;

use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\DateTime\DateTime;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Isbn;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Framework\Testing\factory;

final class ModelFactoryTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function make(): void
    {
        $book = factory(Book::class)->make();

        $this->assertInstanceOf(Book::class, $book);
        // @phpstan-ignore-next-line
        $this->assertNotNull($book->title);
    }

    #[Test]
    public function make_with_data(): void
    {
        $author = new Author(name: 'Brent');

        $factory = factory(Book::class)->with(author: $author);

        $a = $factory->with(title: 'A')->make();
        $b = $factory->with(title: 'B')->make();

        $this->assertSame('A', $a->title);
        $this->assertSame($author, $a->author);
        $this->assertSame('B', $b->title);
        $this->assertSame($author, $b->author);
    }

    #[Test]
    public function with_is_immutable(): void
    {
        $factory = factory(Book::class)->with(title: 'A');

        $factory->with(title: 'B');

        $a = $factory->make();

        $this->assertSame('A', $a->title);
    }

    #[Test]
    public function with_nested_factories(): void
    {
        $bookFactory = factory(Book::class);

        $authorFactory = factory(Author::class)->with(name: 'Brent');

        $bookFactory = $bookFactory->with(author: $authorFactory);

        $book = $bookFactory->make();

        $this->assertSame('Brent', $book->author->name);
    }

    #[Test]
    public function times(): void
    {
        $books = factory(Book::class)->times(3)->make();

        $this->assertCount(3, $books);
    }

    #[Test]
    public function times_with_items(): void
    {
        $books = factory(Book::class)->times([
            ['title' => 'A'],
            ['title' => 'B'],
            ['title' => 'C'],
        ])->make();

        $this->assertCount(3, $books);

        $this->assertSame('A', $books[0]->title);
        $this->assertSame('B', $books[1]->title);
        $this->assertSame('C', $books[2]->title);
    }

    #[Test]
    public function times_with_nested_factories(): void
    {
        $authorFactory = factory(Author::class)->with(name: 'Brent');

        $books = factory(Book::class)->times([
            ['author' => $authorFactory->with(name: 'Roose')],
            ['author' => $authorFactory],
            ['author' => $authorFactory],
        ])->make();

        $this->assertSame('Roose', $books[0]->author->name);
        $this->assertSame('Brent', $books[1]->author->name);
        $this->assertSame('Brent', $books[2]->author->name);
    }

    #[Test]
    public function save_to_the_database(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $book = factory(Book::class)->save();

        $this->database->assertTableHasRow('books', id: $book->id, title: $book->title);
    }

    #[Test]
    public function save_to_the_database_with_nested_relation(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        factory(Book::class)->with(author: factory(Author::class)->with(name: 'Brent'))->save();

        $this->database->assertTableHasRow('authors', name: 'Brent');
    }

    #[Test]
    public function items_save_to_the_database(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        [$a, $b] = factory(Book::class)->times(2)->save();

        $this->database->assertTableHasRow('books', id: $a->id, title: $a->title);
        $this->database->assertTableHasRow('books', id: $b->id, title: $b->title);
    }

    #[Test]
    public function make_with_nested_object(): void
    {
        $isbn = factory(Isbn::class)->make();

        $this->assertInstanceOf(Book::class, $isbn->book);
    }

    #[Test]
    public function make_with_enum(): void
    {
        $author = factory(AuthorWithEnum::class)->make();

        $this->assertInstanceOf(AuthorType::class, $author->type);
    }

    #[Test]
    public function with_datetime(): void
    {
        $withDateTime = factory(WithDateTime::class)->make();

        $this->assertInstanceOf(DateTime::class, $withDateTime->tempestDate);
        $this->assertInstanceOf(DateTimeImmutable::class, $withDateTime->phpDate);
    }
}

class AuthorWithEnum
{
    public AuthorType $type;
}

class WithDateTime
{
    public DateTime $tempestDate;

    public DateTimeImmutable $phpDate;
}
