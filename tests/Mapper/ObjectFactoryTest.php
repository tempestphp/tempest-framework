<?php

declare(strict_types=1);

namespace Tests\Tempest\Mapper;

use App\Modules\Books\Author;
use App\Modules\Books\Book;
use Tempest\Interfaces\Caster;
use Tempest\ORM\Attributes\CastWith;
use Tempest\ORM\MissingValuesException;
use Tests\Tempest\TestCase;

class ObjectFactoryTest extends TestCase
{
    /** @test */
    public function make_object()
    {
        $author = make(Author::class)->from([
            'id' => 1,
            'name' => 'test',
        ]);

        $this->assertSame('test', $author->name);
        $this->assertSame(1, $author->id->id);
    }

    /** @test */
    public function make_object_with_has_many_relation()
    {
        $author = make(Author::class)->from([
            'name' => 'test',
            'books' => [
                ['title' => 'a'],
                ['title' => 'b'],
            ]
        ]);

        $this->assertSame('test', $author->name);
        $this->assertCount(2, $author->books);
        $this->assertSame('a', $author->books[0]->title);
        $this->assertSame('b', $author->books[1]->title);
        $this->assertSame('test', $author->books[0]->author->name);
    }

    /** @test */
    public function make_object_with_one_to_one_relation()
    {
        $book = make(Book::class)->from([
            'title' => 'test',
            'author' => [
                'name' => 'author',
            ]
        ]);

        $this->assertSame('test', $book->title);
        $this->assertSame('author', $book->author->name);
        $this->assertSame('test', $book->author->books[0]->title);
    }

    /** @test */
    public function make_object_with_missing_values_throws_exception()
    {
        $this->expectException(MissingValuesException::class);

        make(Book::class)->from([
            'title' => 'test',
            'author' => [
            ]
        ]);
    }

    /** @test */
    public function test_caster_on_field()
    {
        $object = make(ObjectFactoryA::class)->from([
            'prop' => [],
        ]);

        $this->assertSame('a', $object->prop);
    }
}

class ObjectFactoryA
{
    #[CastWith(ObjectFactoryACaster::class)]
    public string $prop;
}

class ObjectFactoryACaster implements Caster
{
    public function cast(mixed $input): mixed
    {
        return 'a';
    }
}