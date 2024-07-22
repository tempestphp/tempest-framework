<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Mappers;

use Tempest\Database\Id;
use Tempest\Database\Query;
use function Tempest\make;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class QueryMapperTest extends FrameworkIntegrationTestCase
{
    public function test_create_query()
    {
        $author = Author::new(name: 'test');

        $query = make(Query::class)->from($author);

        $table = Author::table();

        $this->assertSame("INSERT INTO {$table} (name) VALUES (:name);", $query->getSql());
        $this->assertSame(['name' => 'test'], $query->bindings);
    }

    public function test_create_query_with_nested_relation()
    {
        $book = Book::new(
            title: 'Book Title',
            author: Author::new(
                name: 'Author Name',
            ),
        );

        $query = make(Query::class)->from($book);

        $bookTable = Book::table();

        $this->assertSame("INSERT INTO {$bookTable} (title, author_id) VALUES (:title, :author_id);", $query->getSql());
        $this->assertSame(['title', 'author_id'], array_keys($query->bindings));
        $this->assertSame('Book Title', $query->bindings['title']);

        $authorTable = Author::table();

        $authorQuery = $query->bindings['author_id'];
        $this->assertInstanceOf(Query::class, $authorQuery);
        $this->assertSame("INSERT INTO {$authorTable} (name) VALUES (:name);", $authorQuery->getSql());
        $this->assertSame('Author Name', $authorQuery->bindings['name']);
    }

    public function test_update_query()
    {
        $author = Author::new(id: new Id(1), name: 'other');

        $query = make(Query::class)->from($author);

        $table = Author::table();

        $this->assertSame("UPDATE {$table} SET name = :name WHERE id = :id;", $query->getSql());
        $this->assertSame(['name' => 'other', 'id' => $author->id], $query->bindings);
    }
}
