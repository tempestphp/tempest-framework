<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Tempest\Database\Exceptions\MissingRelation;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Models\A;
use Tests\Tempest\Fixtures\Models\B;
use Tests\Tempest\Fixtures\Models\C;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\ORM\Migrations\CreateATable;
use Tests\Tempest\Integration\ORM\Migrations\CreateBTable;
use Tests\Tempest\Integration\ORM\Migrations\CreateCTable;

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

    public function test_creating_many_and_saving_preserves_model_id()
    {
        $this->migrate(
            CreateMigrationsTable::class,
            FooMigration::class,
        );

        $a = Foo::create(
            bar: 'a',
        );
        $b = Foo::create(
            bar: 'b',
        );

        $this->assertEquals(1, $a->id->id);
        $a->save();
        $this->assertEquals(1, $a->id->id);
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
                type: AuthorType::B,
            ),
        );

        $book = $book->save();

        $book = Book::find($book->id, relations: ['author']);

        $this->assertEquals(1, $book->id->id);
        $this->assertSame('Book Title', $book->title);
        $this->assertSame(AuthorType::B, $book->author->type);
        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertSame('Author Name', $book->author->name);
        $this->assertEquals(1, $book->author->id->id);
    }

    public function test_all_with_relations(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(
            title: 'Book Title',
            author: new Author(
                name: 'Author Name',
                type: AuthorType::B,
            ),
        )->save();

        $books = Book::all(relations: [
            'author',
        ]);

        $this->assertCount(1, $books);

        $book = $books[0];

        $this->assertEquals(1, $book->id->id);
        $this->assertSame('Book Title', $book->title);
        $this->assertSame(AuthorType::B, $book->author->type);
        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertSame('Author Name', $book->author->name);
        $this->assertEquals(1, $book->author->id->id);
    }

    public function test_missing_relation_exception(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        (new A(
            b: new B(
                c: new C(name: 'test')
            )
        ))->save();

        $a = A::query()->first();

        $this->expectException(MissingRelation::class);

        $b = $a->b;
    }

    public function test_nested_relations(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        (new A(
            b: new B(
                c: new C(name: 'test')
            )
        ))->save();

        $a = A::query()->with('b.c')->first();
        $this->assertSame('test', $a->b->c->name);

        $a = A::query()->with('b.c')->all()[0];
        $this->assertSame('test', $a->b->c->name);
    }

    public function test_no_result(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        $this->assertNull(A::query()->first());
    }

    public function test_update_or_create(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(
            title: 'A',
            author: new Author(
                name: 'Author Name',
                type: AuthorType::B,
            ),
        )->save();

        Book::updateOrCreate(
            ['title' => 'A'],
            ['title' => 'B'],
        );

        $this->assertNull(Book::query()->where('title = :title')->first(title: 'A'));
        $this->assertNotNull(Book::query()->where('title = :title')->first(title: 'B'));
    }
}
