<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Builder\QueryBuilders\WhereGroupBuilder;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreateIsbnTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Fixtures\Modules\Books\Models\Isbn;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class WhereHasInWhereGroupTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function where_has_in_where_group_compiles_exists_subquery(): void
    {
        $sql = Book::select()
            ->whereGroup(callback: function (WhereGroupBuilder $group): void {
                $group->whereHas(relation: 'chapters');
            })
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS books.id, books.title AS books.title, books.author_id AS books.author_id FROM books WHERE EXISTS (SELECT 1 FROM chapters WHERE chapters.book_id = books.id)',
            $sql,
        );
    }

    #[Test]
    public function or_where_has_in_where_group_compiles_or_exists_wrapped_in_parentheses(): void
    {
        $sql = Book::select()
            ->whereGroup(callback: function (WhereGroupBuilder $group): void {
                $group
                    ->whereHas(relation: 'chapters')
                    ->orWhereHas(relation: 'isbn');
            })
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS books.id, books.title AS books.title, books.author_id AS books.author_id FROM books WHERE (EXISTS (SELECT 1 FROM chapters WHERE chapters.book_id = books.id) OR EXISTS (SELECT 1 FROM isbns WHERE isbns.book_id = books.id))',
            $sql,
        );
    }

    #[Test]
    public function or_where_doesnt_have_in_where_group_compiles_or_not_exists(): void
    {
        $sql = Book::select()
            ->whereGroup(callback: function (WhereGroupBuilder $group): void {
                $group
                    ->whereHas(relation: 'chapters')
                    ->orWhereDoesntHave(relation: 'isbn');
            })
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS books.id, books.title AS books.title, books.author_id AS books.author_id FROM books WHERE (EXISTS (SELECT 1 FROM chapters WHERE chapters.book_id = books.id) OR NOT EXISTS (SELECT 1 FROM isbns WHERE isbns.book_id = books.id))',
            $sql,
        );
    }

    #[Test]
    public function where_has_in_where_group_with_callback_compiles_constrained_subquery(): void
    {
        $sql = Book::select()
            ->whereGroup(callback: function (WhereGroupBuilder $group): void {
                $group->whereHas(
                    relation: 'chapters',
                    callback: function ($query): void {
                        $query->whereField(field: 'title', value: 'Chapter 1');
                    },
                );
            })
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS books.id, books.title AS books.title, books.author_id AS books.author_id FROM books WHERE EXISTS (SELECT 1 FROM chapters WHERE chapters.book_id = books.id AND chapters.title = ?)',
            $sql,
        );
    }

    #[Test]
    public function where_has_in_or_where_group_combines_with_outer_where(): void
    {
        $sql = Book::select()
            ->whereField(field: 'title', value: 'LOTR 1')
            ->orWhereGroup(callback: function (WhereGroupBuilder $group): void {
                $group
                    ->whereHas(relation: 'chapters')
                    ->orWhereHas(relation: 'isbn');
            })
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS books.id, books.title AS books.title, books.author_id AS books.author_id FROM books WHERE books.title = ? OR (EXISTS (SELECT 1 FROM chapters WHERE chapters.book_id = books.id) OR EXISTS (SELECT 1 FROM isbns WHERE isbns.book_id = books.id))',
            $sql,
        );
    }

    #[Test]
    public function or_where_has_in_where_group_returns_books_matching_either_relation(): void
    {
        $this->seed();

        $books = Book::select()
            ->whereGroup(callback: function (WhereGroupBuilder $group): void {
                $group
                    ->whereHas(relation: 'chapters')
                    ->orWhereHas(relation: 'isbn');
            })
            ->orderBy(field: 'id')
            ->all();

        $this->assertCount(3, $books);
        $this->assertSame('LOTR 1', $books[0]->title);
        $this->assertSame('LOTR 2', $books[1]->title);
        $this->assertSame('Timeline Taxi', $books[2]->title);
    }

    private function seed(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateIsbnTable::class,
        );

        $brent = Author::create(name: 'Brent');
        $tolkien = Author::create(name: 'Tolkien');

        $lotr1 = Book::create(title: 'LOTR 1', author: $tolkien);
        $lotr2 = Book::create(title: 'LOTR 2', author: $tolkien);
        Book::create(title: 'LOTR 3', author: $tolkien);
        $timelineTaxi = Book::create(title: 'Timeline Taxi', author: $brent);

        Chapter::create(title: 'Chapter 1', book: $lotr1);
        Chapter::create(title: 'Chapter 1', book: $lotr2);

        Isbn::create(value: 'isbn-lotr-1', book: $lotr1);
        Isbn::create(value: 'isbn-tt', book: $timelineTaxi);
    }
}
