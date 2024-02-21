<?php

declare(strict_types=1);

namespace Tests\Tempest\ORM;

use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use Tempest\Interface\Migration;
use Tempest\Interface\Model;
use Tempest\ORM\BaseModel;
use Tests\Tempest\TestCase;

class BaseModelTest extends TestCase
{
    /** @test */
    public function create_and_update_model()
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

    /** @test */
    public function complex_query()
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

class Foo implements Model
{
    use BaseModel;

    public string $bar;
}

class FooMigration implements Migration
{
    public function getName(): string
    {
        return 'foo';
    }

    public function up(): Query|null
    {
        return new Query("CREATE TABLE Foo (
            `id` INTEGER PRIMARY KEY AUTOINCREMENT,
            `bar` TEXT
        )");
    }

    public function down(): Query|null
    {
        return null;
    }
}
