<?php

declare(strict_types=1);

use App\Modules\Books\Models\Author;
use App\Modules\Books\Models\Book;
use Tempest\Database\Id;
use Tempest\Database\Query;
use Tests\Tempest\TestCase;
use function Tempest\make;

uses(TestCase::class);

test('create query', function () {
	$author = Author::new(name: 'test');

	$query = make(Query::class)->from($author);

	$table = Author::table();

	expect($query->getSql())->toBe("INSERT INTO {$table} (name) VALUES (:name);");
	expect($query->bindings)->toBe(['name' => 'test']);
});

test('create query with nested relation', function () {
	$book = Book::new(
		title: 'Book Title',
		author: Author::new(
			name: 'Author Name',
		),
	);

	$query = make(Query::class)->from($book);

	$bookTable = Book::table();

	expect($query->getSql())->toBe("INSERT INTO {$bookTable} (title, author_id) VALUES (:title, :author_id);");
	expect(array_keys($query->bindings))->toBe(['title', 'author_id']);
	expect($query->bindings['title'])->toBe('Book Title');

	$authorTable = Author::table();

	$authorQuery = $query->bindings['author_id'];
	expect($authorQuery)->toBeInstanceOf(Query::class);
	expect($authorQuery->getSql())->toBe("INSERT INTO {$authorTable} (name) VALUES (:name);");
	expect($authorQuery->bindings['name'])->toBe('Author Name');
});

test('update query', function () {
	$author = Author::new(id: new Id(1), name: 'other');

	$query = make(Query::class)->from($author);

	$table = Author::table();

	expect($query->getSql())->toBe("UPDATE {$table} SET name = :name WHERE id = 1;");
	expect($query->bindings)->toBe(['name' => 'other']);
});
