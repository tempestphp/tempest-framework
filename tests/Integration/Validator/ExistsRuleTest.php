<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Validator;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Validation\Rules\Exists;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ExistsRuleTest extends FrameworkIntegrationTestCase
{
    #[PreCondition]
    protected function configure(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );
    }

    #[Test]
    public function existing_record_return_true(): void
    {
        $book = Book::create(title: 'Timeline Taxi');

        $this->assertTrue(new Exists(Book::class)->isValid($book->id));
        $this->assertTrue(new Exists('books', column: 'id')->isValid($book->id));
        $this->assertTrue(new Exists('books', column: 'title')->isValid('Timeline Taxi'));
    }

    #[Test]
    public function non_existent_record_returns_false(): void
    {
        Book::create(title: 'Timeline Taxi');

        $this->assertFalse(new Exists(Book::class)->isValid(99999));
        $this->assertFalse(new Exists(Book::class)->isValid(12345));
        $this->assertFalse(new Exists(Book::class, column: 'title')->isValid('Timeline Taxi 2'));
    }

    #[Test]
    public function validates_multiple_existing_records(): void
    {
        $book1 = Book::create(title: 'The Lord of the Rings');
        $book2 = Book::create(title: 'The Silmarillion');
        $book3 = Book::create(title: 'Unfinished Tales');

        $this->assertTrue(new Exists(Book::class)->isValid($book1->id));
        $this->assertTrue(new Exists(Book::class)->isValid($book2->id));
        $this->assertTrue(new Exists(Book::class)->isValid($book3->id));
        $this->assertFalse(new Exists(Book::class)->isValid(99999));
    }
}
