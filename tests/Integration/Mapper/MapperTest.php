<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use function Tempest\make;
use function Tempest\map;
use Tempest\ORM\Exceptions\MissingValuesException;
use Tempest\Testing\IntegrationTest;
use Tempest\Validation\Exceptions\ValidationException;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectFactoryA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectFactoryAMigration;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectFactoryWithValidation;

/**
 * @internal
 * @small
 */
class MapperTest extends IntegrationTest
{
    public function test_make_object_from_class_string()
    {
        $author = make(Author::class)->from([
            'id' => 1,
            'name' => 'test',
        ]);

        $this->assertSame('test', $author->name);
        $this->assertSame(1, $author->id->id);
    }

    public function test_make_collection()
    {
        $authors = make(Author::class)->collection()->from([
            [
                'id' => 1,
                'name' => 'test',
            ],
        ]);

        $this->assertCount(1, $authors);
        $this->assertSame('test', $authors[0]->name);
        $this->assertSame(1, $authors[0]->id->id);
    }

    public function test_make_object_from_existing_object()
    {
        $author = Author::new(
            name: 'original',
        );

        $author = make($author)->from([
            'id' => 1,
            'name' => 'other',
        ]);

        $this->assertSame('other', $author->name);
        $this->assertSame(1, $author->id->id);
    }

    public function test_make_object_with_map_to()
    {
        $author = Author::new(
            name: 'original',
        );

        $author = map([
            'id' => 1,
            'name' => 'other',
        ])->to($author);

        $this->assertSame('other', $author->name);
        $this->assertSame(1, $author->id->id);
    }

    public function test_make_object_with_has_many_relation()
    {
        $author = make(Author::class)->from([
            'name' => 'test',
            'books' => [
                ['title' => 'a'],
                ['title' => 'b'],
            ],
        ]);

        $this->assertSame('test', $author->name);
        $this->assertCount(2, $author->books);
        $this->assertSame('a', $author->books[0]->title);
        $this->assertSame('b', $author->books[1]->title);
        $this->assertSame('test', $author->books[0]->author->name);
    }

    public function test_make_object_with_one_to_one_relation()
    {
        $book = make(Book::class)->from([
            'title' => 'test',
            'author' => [
                'name' => 'author',
            ],
        ]);

        $this->assertSame('test', $book->title);
        $this->assertSame('author', $book->author->name);
        $this->assertSame('test', $book->author->books[0]->title);
    }

    public function test_make_object_with_missing_values_throws_exception()
    {
        $this->expectException(MissingValuesException::class);

        make(Book::class)->from([
            'title' => 'test',
            'author' => [
            ],
        ]);
    }

    public function test_caster_on_field()
    {
        $object = make(ObjectFactoryA::class)->from([
            'prop' => [],
        ]);

        $this->assertSame('casted', $object->prop);
    }

    public function test_single_with_query()
    {
        $this->migrate(
            CreateMigrationsTable::class,
            ObjectFactoryAMigration::class,
        );

        ObjectFactoryA::create(
            prop: 'a',
        );

        ObjectFactoryA::create(
            prop: 'b',
        );

        $a = make(ObjectFactoryA::class)->from(new Query(
            "SELECT * FROM ObjectFactoryA WHERE id = :id",
            [
                'id' => 1,
            ],
        ));

        $this->assertSame(1, $a->id->id);
        $this->assertSame('casted', $a->prop);

        $collection = make(ObjectFactoryA::class)->from(new Query(
            "SELECT * FROM ObjectFactoryA",
        ));

        $this->assertCount(2, $collection);
        $this->assertSame('casted', $collection[0]->prop);
        $this->assertSame('casted', $collection[1]->prop);
    }

    public function test_validation()
    {
        $this->expectException(ValidationException::class);

        map(['prop' => 'a'])->to(ObjectFactoryWithValidation::class);
    }
}
