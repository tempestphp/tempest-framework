<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http\Rules;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Exceptions\QueryWasInvalid;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Validation\Rules\Exists;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ExistsRuleTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function validates_existing_record_returns_true(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $book = Book::create(title: 'Timeline Taxi');

        $rule = new Exists(Book::class);

        $this->assertTrue($rule->isValid($book->id));
    }

    #[Test]
    public function validates_non_existent_record_returns_false(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $rule = new Exists(Book::class);

        $this->assertFalse($rule->isValid(99999));
        $this->assertFalse($rule->isValid(12345));
    }

    #[Test]
    public function validates_multiple_existing_records(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $book1 = Book::create(title: 'The Lord of the Rings');
        $book2 = Book::create(title: 'The Silmarillion');
        $book3 = Book::create(title: 'Unfinished Tales');

        $rule = new Exists(Book::class);

        $this->assertTrue($rule->isValid($book1->id->id));
        $this->assertTrue($rule->isValid($book2->id->id));
        $this->assertTrue($rule->isValid($book3->id->id));

        $this->assertFalse($rule->isValid(99999));
    }

    #[Test]
    public function validates_different_model_types(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::create(name: 'B. Roose');
        $book = Book::create(title: 'Timeline Taxi');

        $authorRule = new Exists(Author::class);
        $bookRule = new Exists(Book::class);

        $this->assertTrue($authorRule->isValid($author->id->id));
        $this->assertTrue($bookRule->isValid($book->id->id));

        $this->assertFalse($authorRule->isValid(99999));
        $this->assertFalse($bookRule->isValid(99999));

        $author2 = Author::create(name: 'B. Roose');
        $book2 = Book::create(title: 'Timeline Taxi 2');

        $this->assertTrue($authorRule->isValid($author2->id->id));
        $this->assertTrue($bookRule->isValid($book2->id->id));
    }

    #[Test]
    public function validates_edge_cases_with_large_id_numbers(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $rule = new Exists(Book::class);

        $this->assertFalse($rule->isValid(PHP_INT_MAX));
        $this->assertFalse($rule->isValid(999999999));
        $this->assertFalse($rule->isValid(2147483647)); // Max 32-bit integer
    }

    #[Test]
    public function validates_after_record_deletion(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $book = Book::create(title: 'Timeline Taxi Draft');
        $bookId = $book->id->id;

        $rule = new Exists(Book::class);

        $this->assertTrue($rule->isValid($bookId));

        $book->delete();

        $this->assertFalse($rule->isValid($bookId));
    }

    #[Test]
    public function validates_with_sequential_id_creation(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $rule = new Exists(Book::class);
        $createdIds = [];

        for ($i = 1; $i <= 5; $i++) {
            $book = Book::create(title: "Book {$i}");
            $createdIds[] = $book->id->id;

            $this->assertTrue($rule->isValid($book->id->id));
        }

        foreach ($createdIds as $id) {
            $this->assertTrue($rule->isValid($id));
        }

        $maxId = max($createdIds);
        $this->assertFalse($rule->isValid($maxId + 1));
        $this->assertFalse($rule->isValid($maxId + 100));
    }
}
