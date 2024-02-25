<?php

declare(strict_types=1);

use App\Migrations\CreateAuthorTable;
use App\Migrations\CreateBookTable;
use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\ORM\Foo;
use Tests\Tempest\ORM\FooMigration;
use Tests\Tempest\TestCase;

uses(TestCase::class);

test('create and update model', function () {
	$this->migrate(
		CreateMigrationsTable::class,
		FooMigration::class,
	);

	$foo = Foo::create(
		bar: 'baz',
	);

	expect($foo->bar)->toBe('baz');
	expect($foo->id)->toBeInstanceOf(Id::class);

	$foo = Foo::find($foo->id);

	expect($foo->bar)->toBe('baz');
	expect($foo->id)->toBeInstanceOf(Id::class);

	$foo->update(
		bar: 'boo',
	);

	$foo = Foo::find($foo->id);

	expect($foo->bar)->toBe('boo');
});

test('complex query', function () {
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

	expect($book->id->id)->toEqual(1);
	expect($book->title)->toBe('Book Title');
	expect($book->author)->toBeInstanceOf(Author::class);
	expect($book->author->name)->toBe('Author Name');
	expect($book->author->id->id)->toEqual(1);
});
