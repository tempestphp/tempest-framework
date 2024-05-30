<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class IsModelTest extends FrameworkIntegrationTestCase
{
    public function test_create_and_update_model()
    {
        $this->migrate(
            CreateMigrationsTable::class,
            FooMigration::class,
        );

        $foo = Foo::create(
            bar: 'baz',
        );

        $this->assertSame('baz', $foo->bar);
        $this->assertInstanceOf(Id::class, $foo->id);

        $foo = Foo::find($foo->id);

        $this->assertSame('baz', $foo->bar);
        $this->assertInstanceOf(Id::class, $foo->id);

        $foo->update(
            bar: 'boo',
        );

        $foo = Foo::find($foo->id);

        $this->assertSame('boo', $foo->bar);
    }

    public function test_complex_query()
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $book = Book::new(
            title: 'Book Title',
            author: new Author(
                name: 'Author Name',
            ),
        );

        $book = $book->save();

        $book = Book::find($book->id, relations: [
            Author::class,
        ]);

        $this->assertEquals(1, $book->id->id);
        $this->assertSame('Book Title', $book->title);
        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertSame('Author Name', $book->author->name);
        $this->assertEquals(1, $book->author->id->id);
    }
}
