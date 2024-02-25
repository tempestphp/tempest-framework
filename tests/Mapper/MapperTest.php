<?php

declare(strict_types=1);

use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Query;
use function Tempest\make;
use function Tempest\map;
use Tempest\ORM\Exceptions\MissingValuesException;
use Tempest\Validation\Exceptions\ValidationException;
use Tests\Tempest\Mapper\Fixtures\ObjectFactoryA;
use Tests\Tempest\Mapper\Fixtures\ObjectFactoryAMigration;
use Tests\Tempest\Mapper\Fixtures\ObjectFactoryWithValidation;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('make object from class string', function () {
    $author = make(Author::class)->from([
        'id' => 1,
        'name' => 'test',
    ]);

    expect($author->name)->toBe('test');
    expect($author->id->id)->toBe(1);
});

test('make collection', function () {
    $authors = make(Author::class)->collection()->from([
        [
            'id' => 1,
            'name' => 'test',
        ],
    ]);

    expect($authors)->toHaveCount(1);
    expect($authors[0]->name)->toBe('test');
    expect($authors[0]->id->id)->toBe(1);
});

test('make object from existing object', function () {
    $author = Author::new(
        name: 'original',
    );

    $author = make($author)->from([
        'id' => 1,
        'name' => 'other',
    ]);

    expect($author->name)->toBe('other');
    expect($author->id->id)->toBe(1);
});

test('make object with map to', function () {
    $author = Author::new(
        name: 'original',
    );

    $author = map([
        'id' => 1,
        'name' => 'other',
    ])->to($author);

    expect($author->name)->toBe('other');
    expect($author->id->id)->toBe(1);
});

test('make object with has many relation', function () {
    $author = make(Author::class)->from([
        'name' => 'test',
        'books' => [
            ['title' => 'a'],
            ['title' => 'b'],
        ],
    ]);

    expect($author->name)->toBe('test');
    expect($author->books)->toHaveCount(2);
    expect($author->books[0]->title)->toBe('a');
    expect($author->books[1]->title)->toBe('b');
    expect($author->books[0]->author->name)->toBe('test');
});

test('make object with one to one relation', function () {
    $book = make(Book::class)->from([
        'title' => 'test',
        'author' => [
            'name' => 'author',
        ],
    ]);

    expect($book->title)->toBe('test');
    expect($book->author->name)->toBe('author');
    expect($book->author->books[0]->title)->toBe('test');
});

test('make object with missing values throws exception', function () {
    $this->expectException(MissingValuesException::class);

    make(Book::class)->from([
        'title' => 'test',
        'author' => [
        ],
    ]);
});

test('caster on field', function () {
    $object = make(ObjectFactoryA::class)->from([
        'prop' => [],
    ]);

    expect($object->prop)->toBe('casted');
});

test('single with query', function () {
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

    expect($a->id->id)->toBe(1);
    expect($a->prop)->toBe('casted');

    $collection = make(ObjectFactoryA::class)->from(new Query(
        "SELECT * FROM ObjectFactoryA",
    ));

    expect($collection)->toHaveCount(2);
    expect($collection[0]->prop)->toBe('casted');
    expect($collection[1]->prop)->toBe('casted');
});

test('validation', function () {
    $this->expectException(ValidationException::class);

    map(['prop' => 'a'])->to(ObjectFactoryWithValidation::class);
});
