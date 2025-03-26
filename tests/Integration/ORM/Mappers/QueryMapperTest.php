<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM\Mappers;

use Tempest\Database\Id;
use Tempest\Database\Query;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\make;

/**
 * @internal
 */
final class QueryMapperTest extends FrameworkIntegrationTestCase
{
    public function test_create_query(): void
    {
        $author = Author::new(name: 'test');

        $query = make(Query::class)->from($author);

        $this->assertSame('INSERT INTO `authors` (name) VALUES (:name);', $query->getSql());
        $this->assertSame(['name' => 'test'], $query->bindings);
    }

    public function test_create_query_with_nested_relation(): void
    {
        $book = Book::new(
            title: 'Book Title',
            author: Author::new(
                name: 'Author Name',
            ),
        );

        $query = make(Query::class)->from($book);

        $this->assertSame('INSERT INTO `books` (title, author_id) VALUES (:title, :author_id);', $query->getSql());
        $this->assertSame(['title', 'author_id'], array_keys($query->bindings));
        $this->assertSame('Book Title', $query->bindings['title']);

        $authorQuery = $query->bindings['author_id'];
        $this->assertInstanceOf(Query::class, $authorQuery);
        $this->assertSame('INSERT INTO `authors` (name) VALUES (:name);', $authorQuery->getSql());
        $this->assertSame('Author Name', $authorQuery->bindings['name']);
    }

    public function test_update_query(): void
    {
        $author = Author::new(id: new Id(1), name: 'other');

        $query = make(Query::class)->from($author);

        $this->assertSame('UPDATE `authors` SET name = :name WHERE id = :id;', $query->getSql());
        $this->assertSame(['name' => 'other', 'id' => $author->id], $query->bindings);
    }
}
