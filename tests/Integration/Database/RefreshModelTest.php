<?php

namespace Integration\Database;

use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\Database\query;

final class RefreshModelTest extends FrameworkIntegrationTestCase
{
    public function test_refresh_works_for_models_with_unloaded_relation(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateBookTable::class,
            CreateAuthorTable::class,
        );

        $author = Author::create(
            name: 'Brent Roose',
        );

        $book = Book::create(
            title: 'Timeline Taxi',
            author: $author,
        );

        // Get user without loading the profile relation
        $book = Book::get($book->id);

        $this->assertNull($book->author);

        // Update the user's name in the database
        query(Book::class)
            ->update(
                title: 'Timeline Taxi 2',
            )
            ->where('id', $book->id)
            ->execute();

        // Refresh should work even with unloaded relations
        $book->refresh();

        $this->assertSame('Timeline Taxi 2', $book->title);
        $this->assertNull($book->author); // Relation should still be unloaded

        // Load the relation
        $book->load('author');

        $this->assertInstanceOf(Author::class, $book->author);

        $book->refresh();

        $this->assertInstanceOf(Author::class, $book->author);
    }
}