<?php

declare(strict_types=1);

namespace Tests\Tempest\ORM;

use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use App\Modules\Books\Author;
use App\Modules\Books\Book;
use Tempest\Database\Builder\IdRow;
use Tempest\Database\Builder\TableBuilder;
use Tempest\Database\Builder\TextRow;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use Tempest\Interfaces\Migration;
use Tempest\Interfaces\Model;
use Tempest\ORM\BaseModel;
use Tests\Tempest\TestCase;

class BaseModelTest extends TestCase
{
    /** @test */
    public function create_and_update_model()
    {
        $this->migrate(
            CreateMigrationsTable::class,
            FooMigration::class
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
        $this->markTestSkipped();
        $this->migrate(
            CreateMigrationsTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $a = Author::create(name: 'A');
        $b = Author::create(name: 'B');
        Book::create(title: 'A1', author: $a);
        Book::create(title: 'A2', author: $a);
        Book::create(title: 'B1', author: $b);
        Book::create(title: 'B2', author: $b);

        $authors = make(Author::class)->collection()->from(new Query(<<<SQL
            SELECT * 
            FROM Author
            INNER JOIN Book on Author.id = Book.author_id
            SQL)
        );
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

    public function up(TableBuilder $builder): TableBuilder
    {
        return $builder
            ->name(Foo::table())
            ->add(new IdRow())
            ->add(new TextRow('bar'))
            ->create();
    }

    public function down(TableBuilder $builder): TableBuilder
    {
        return $builder
            ->name(Foo::table())
            ->drop();
    }
}
