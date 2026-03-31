<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Builder\WhereOperator;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookReviewTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTagTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreateIsbnTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Migrations\CreateReviewerTable;
use Tests\Tempest\Fixtures\Migrations\CreateTagTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\BookReview;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Fixtures\Modules\Books\Models\Isbn;
use Tests\Tempest\Fixtures\Modules\Books\Models\Reviewer;
use Tests\Tempest\Fixtures\Modules\Books\Models\Tag;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class WhereHasTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function where_has_compiles_exists_subquery(): void
    {
        $sql = Author::select()
            ->whereHas(relation: 'books')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id)',
            $sql,
        );
    }

    #[Test]
    public function where_has_with_callback_compiles_constrained_subquery(): void
    {
        $sql = Author::select()
            ->whereHas(
                relation: 'books',
                callback: function (
                    SelectQueryBuilder $query,
                ): void {
                    $query->whereField(
                        field: 'title',
                        value: 'LOTR 1',
                    );
                },
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id AND books.title = ?)',
            $sql,
        );
    }

    #[Test]
    public function where_doesnt_have_compiles_not_exists_subquery(): void
    {
        $sql = Author::select()
            ->whereDoesntHave(relation: 'books')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE NOT EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id)',
            $sql,
        );
    }

    #[Test]
    public function where_doesnt_have_with_callback_compiles_constrained_subquery(): void
    {
        $sql = Author::select()
            ->whereDoesntHave(
                relation: 'books',
                callback: function (
                    SelectQueryBuilder $query,
                ): void {
                    $query->whereField(
                        field: 'title',
                        value: 'LOTR 1',
                    );
                },
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE NOT EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id AND books.title = ?)',
            $sql,
        );
    }

    #[Test]
    public function where_has_combined_with_other_where_clauses(): void
    {
        $sql = Author::select()
            ->whereField(
                field: 'name',
                value: 'Tolkien',
            )
            ->whereHas(relation: 'books')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE authors.name = ? AND EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id)',
            $sql,
        );
    }

    #[Test]
    public function or_where_has_compiles_or_exists(): void
    {
        $sql = Author::select()
            ->whereField(
                field: 'name',
                value: 'Tolkien',
            )
            ->orWhereHas(relation: 'books')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE authors.name = ? OR EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id)',
            $sql,
        );
    }

    #[Test]
    public function or_where_doesnt_have_compiles_or_not_exists(): void
    {
        $sql = Author::select()
            ->whereField(
                field: 'name',
                value: 'Tolkien',
            )
            ->orWhereDoesntHave(relation: 'books')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE authors.name = ? OR NOT EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id)',
            $sql,
        );
    }

    #[Test]
    public function where_has_on_belongs_to_relation(): void
    {
        $sql = Book::select()
            ->whereHas(relation: 'author')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS books.id, books.title AS books.title, books.author_id AS books.author_id FROM books WHERE EXISTS (SELECT 1 FROM authors WHERE authors.id = books.author_id)',
            $sql,
        );
    }

    #[Test]
    public function where_has_on_belongs_to_with_callback(): void
    {
        $sql = Book::select()
            ->whereHas(
                relation: 'author',
                callback: function (
                    SelectQueryBuilder $query,
                ): void {
                    $query->whereField(
                        field: 'name',
                        value: 'Tolkien',
                    );
                },
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS books.id, books.title AS books.title, books.author_id AS books.author_id FROM books WHERE EXISTS (SELECT 1 FROM authors WHERE authors.id = books.author_id AND authors.name = ?)',
            $sql,
        );
    }

    #[Test]
    public function where_has_with_where_group_callback(): void
    {
        $sql = Author::select()
            ->whereHas(relation: 'books', callback: function (SelectQueryBuilder $query): void {
                $query
                    ->whereField(field: 'title', value: 'LOTR 1')
                    ->orWhereGroup(callback: function ($group): void {
                        $group->whereField(field: 'title', value: 'Timeline Taxi');
                    });
            })
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id AND books.title = ? OR books.title = ?)',
            $sql,
        );
    }

    #[Test]
    public function where_has_with_where_group_callback_returns_correct_results(): void
    {
        $this->seed();

        $authors = Author::select()
            ->whereHas(relation: 'books', callback: function (SelectQueryBuilder $query): void {
                $query->whereGroup(callback: function ($group): void {
                    $group->whereField(field: 'title', value: 'LOTR 1')
                        ->orWhere(field: 'title', value: 'Timeline Taxi');
                });
            })
            ->all();

        $this->assertCount(2, $authors);
        $this->assertSame('Brent', $authors[0]->name);
        $this->assertSame('Tolkien', $authors[1]->name);
    }

    #[Test]
    public function where_has_on_has_one_relation(): void
    {
        $sql = Book::select()
            ->whereHas(relation: 'isbn')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT books.id AS books.id, books.title AS books.title, books.author_id AS books.author_id FROM books WHERE EXISTS (SELECT 1 FROM isbns WHERE isbns.book_id = books.id)',
            $sql,
        );
    }

    #[Test]
    public function where_has_on_belongs_to_many_relation(): void
    {
        $sql = Tag::select()
            ->whereHas(relation: 'books')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT tags.id AS tags.id, tags.label AS tags.label FROM tags WHERE EXISTS (SELECT 1 FROM books_tags WHERE books_tags.tag_id = tags.id)',
            $sql,
        );
    }

    #[Test]
    public function where_has_on_belongs_to_many_with_callback(): void
    {
        $sql = Tag::select()
            ->whereHas(
                relation: 'books',
                callback: function (SelectQueryBuilder $query): void {
                    $query->whereField(
                        field: 'title',
                        value: 'LOTR 1',
                    );
                },
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT tags.id AS tags.id, tags.label AS tags.label FROM tags WHERE EXISTS (SELECT 1 FROM books_tags INNER JOIN books ON books.id = books_tags.book_id WHERE books_tags.tag_id = tags.id AND books.title = ?)',
            $sql,
        );
    }

    #[Test]
    public function where_has_on_belongs_to_many_with_callback_filters_correctly(): void
    {
        $this->seed();

        $tags = Tag::select()
            ->whereHas(
                relation: 'books',
                callback: function (SelectQueryBuilder $query): void {
                    $query->whereField(
                        field: 'title',
                        value: 'LOTR 1',
                    );
                },
            )
            ->all();

        $this->assertCount(1, $tags);
        $this->assertSame('fantasy', $tags[0]->label);
    }

    #[Test]
    public function where_doesnt_have_on_belongs_to_many_with_callback(): void
    {
        $this->seed();

        $tags = Tag::select()
            ->whereDoesntHave(
                relation: 'books',
                callback: function (SelectQueryBuilder $query): void {
                    $query->whereField(
                        field: 'title',
                        value: 'LOTR 1',
                    );
                },
            )
            ->all();

        $this->assertCount(1, $tags);
        $this->assertSame('orphan', $tags[0]->label);
    }

    #[Test]
    public function where_has_on_has_many_through_relation(): void
    {
        $sql = Tag::select()
            ->whereHas(relation: 'reviewers')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT tags.id AS tags.id, tags.label AS tags.label FROM tags WHERE EXISTS (SELECT 1 FROM book_reviews WHERE book_reviews.tag_id = tags.id)',
            $sql,
        );
    }

    #[Test]
    public function where_has_on_has_one_through_relation(): void
    {
        $sql = Tag::select()
            ->whereHas(relation: 'topReviewer')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT tags.id AS tags.id, tags.label AS tags.label FROM tags WHERE EXISTS (SELECT 1 FROM book_reviews WHERE book_reviews.tag_id = tags.id)',
            $sql,
        );
    }

    #[Test]
    public function where_has_on_has_many_through_with_callback(): void
    {
        $sql = Tag::select()
            ->whereHas(
                relation: 'reviewers',
                callback: function (SelectQueryBuilder $query): void {
                    $query->whereField(
                        field: 'name',
                        value: 'Alice',
                    );
                },
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT tags.id AS tags.id, tags.label AS tags.label FROM tags WHERE EXISTS (SELECT 1 FROM book_reviews INNER JOIN reviewers ON reviewers.book_review_id = book_reviews.id WHERE book_reviews.tag_id = tags.id AND reviewers.name = ?)',
            $sql,
        );
    }

    #[Test]
    public function where_has_on_has_many_through_with_callback_filters_correctly(): void
    {
        $this->seed();

        $tags = Tag::select()
            ->whereHas(
                relation: 'reviewers',
                callback: function (SelectQueryBuilder $query): void {
                    $query->whereField(
                        field: 'name',
                        value: 'Alice',
                    );
                },
            )
            ->all();

        $this->assertCount(1, $tags);
        $this->assertSame('fantasy', $tags[0]->label);
    }

    #[Test]
    public function where_has_on_has_one_through_with_callback(): void
    {
        $sql = Tag::select()
            ->whereHas(
                relation: 'topReviewer',
                callback: function (SelectQueryBuilder $query): void {
                    $query->whereField(
                        field: 'name',
                        value: 'Alice',
                    );
                },
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT tags.id AS tags.id, tags.label AS tags.label FROM tags WHERE EXISTS (SELECT 1 FROM book_reviews INNER JOIN reviewers ON reviewers.book_review_id = book_reviews.id WHERE book_reviews.tag_id = tags.id AND reviewers.name = ?)',
            $sql,
        );
    }

    #[Test]
    public function where_has_with_count_compiles_count_subquery(): void
    {
        $sql = Author::select()
            ->whereHas(
                relation: 'books',
                operator: WhereOperator::GREATER_THAN_OR_EQUAL,
                count: 3,
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE (SELECT COUNT(*) FROM books WHERE books.author_id = authors.id) >= 3',
            $sql,
        );
    }

    #[Test]
    public function where_has_with_count_and_callback(): void
    {
        $sql = Author::select()
            ->whereHas(
                relation: 'books',
                callback: function (
                    SelectQueryBuilder $query,
                ): void {
                    $query->whereField(
                        field: 'title',
                        value: 'LOTR 1',
                        operator: 'LIKE',
                    );
                },
                operator: WhereOperator::GREATER_THAN_OR_EQUAL,
                count: 2,
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE (SELECT COUNT(*) FROM books WHERE books.author_id = authors.id AND books.title LIKE ?) >= 2',
            $sql,
        );
    }

    #[Test]
    public function where_has_with_count_equals_zero(): void
    {
        $sql = Author::select()
            ->whereHas(
                relation: 'books',
                operator: WhereOperator::EQUALS,
                count: 0,
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE (SELECT COUNT(*) FROM books WHERE books.author_id = authors.id) = 0',
            $sql,
        );
    }

    #[Test]
    public function or_where_has_with_count(): void
    {
        $sql = Author::select()
            ->whereField(
                field: 'name',
                value: 'Nobody',
            )
            ->orWhereHas(
                relation: 'books',
                operator: WhereOperator::GREATER_THAN_OR_EQUAL,
                count: 3,
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE authors.name = ? OR (SELECT COUNT(*) FROM books WHERE books.author_id = authors.id) >= 3',
            $sql,
        );
    }

    #[Test]
    public function where_has_nested_relation_compiles_nested_exists(): void
    {
        $sql = Author::select()
            ->whereHas(relation: 'books.chapters')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id AND EXISTS (SELECT 1 FROM chapters WHERE chapters.book_id = books.id))',
            $sql,
        );
    }

    #[Test]
    public function where_has_nested_relation_with_callback(): void
    {
        $sql = Author::select()
            ->whereHas(
                relation: 'books.chapters',
                callback: function (
                    SelectQueryBuilder $query,
                ): void {
                    $query->whereField(
                        field: 'title',
                        value: 'Chapter 1',
                    );
                },
            )
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id AND EXISTS (SELECT 1 FROM chapters WHERE chapters.book_id = books.id AND chapters.title = ?))',
            $sql,
        );
    }

    #[Test]
    public function where_doesnt_have_nested_relation(): void
    {
        $sql = Author::select()
            ->whereDoesntHave(relation: 'books.chapters')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT authors.id AS authors.id, authors.name AS authors.name, authors.type AS authors.type, authors.publisher_id AS authors.publisher_id FROM authors WHERE NOT EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id AND EXISTS (SELECT 1 FROM chapters WHERE chapters.book_id = books.id))',
            $sql,
        );
    }

    #[Test]
    public function where_has_returns_models_with_related_records(): void
    {
        $this->seed();

        $authors = Author::select()
            ->whereHas(relation: 'books')
            ->all();

        $this->assertCount(
            2,
            $authors,
        );
        $this->assertSame(
            'Brent',
            $authors[0]->name,
        );
        $this->assertSame(
            'Tolkien',
            $authors[1]->name,
        );
    }

    #[Test]
    public function where_has_with_callback_filters_by_related_records(): void
    {
        $this->seed();

        $authors = Author::select()
            ->whereHas(
                relation: 'books',
                callback: function (
                    SelectQueryBuilder $query,
                ): void {
                    $query->whereField(
                        field: 'title',
                        value: 'Timeline Taxi',
                    );
                },
            )
            ->all();

        $this->assertCount(
            1,
            $authors,
        );
        $this->assertSame(
            'Brent',
            $authors[0]->name,
        );
    }

    #[Test]
    public function where_doesnt_have_returns_models_without_related_records(): void
    {
        $this->seed();

        $authors = Author::select()
            ->whereDoesntHave(relation: 'books')
            ->all();

        $this->assertCount(
            1,
            $authors,
        );
        $this->assertSame(
            'Nobody',
            $authors[0]->name,
        );
    }

    #[Test]
    public function where_has_nested_returns_models_with_deeply_related_records(): void
    {
        $this->seed();

        $authors = Author::select()
            ->whereHas(relation: 'books.chapters')
            ->all();

        $this->assertCount(
            1,
            $authors,
        );
        $this->assertSame(
            'Tolkien',
            $authors[0]->name,
        );
    }

    #[Test]
    public function where_doesnt_have_nested_returns_models_without_deeply_related_records(): void
    {
        $this->seed();

        $authors = Author::select()
            ->whereDoesntHave(relation: 'books.chapters')
            ->all();

        $this->assertCount(
            2,
            $authors,
        );
        $this->assertSame(
            'Brent',
            $authors[0]->name,
        );
        $this->assertSame(
            'Nobody',
            $authors[1]->name,
        );
    }

    #[Test]
    public function where_has_with_count_returns_authors_with_enough_books(): void
    {
        $this->seed();

        $authors = Author::select()
            ->whereHas(
                relation: 'books',
                operator: WhereOperator::GREATER_THAN_OR_EQUAL,
                count: 3,
            )
            ->all();

        $this->assertCount(
            1,
            $authors,
        );
        $this->assertSame(
            'Tolkien',
            $authors[0]->name,
        );
    }

    #[Test]
    public function where_has_with_count_and_callback_filters_correctly(): void
    {
        $this->seed();

        $authors = Author::select()
            ->whereHas(
                relation: 'books',
                callback: function (
                    SelectQueryBuilder $query,
                ): void {
                    $query->whereLike(
                        field: 'title',
                        value: 'LOTR%',
                    );
                },
                operator: WhereOperator::GREATER_THAN_OR_EQUAL,
                count: 2,
            )
            ->all();

        $this->assertCount(
            1,
            $authors,
        );
        $this->assertSame(
            'Tolkien',
            $authors[0]->name,
        );
    }

    #[Test]
    public function where_doesnt_have_with_callback_returns_models_without_matching_related_records(): void
    {
        $this->seed();

        $authors = Author::select()
            ->whereDoesntHave(
                relation: 'books',
                callback: function (
                    SelectQueryBuilder $query,
                ): void {
                    $query->whereField(
                        field: 'title',
                        value: 'Timeline Taxi',
                    );
                },
            )
            ->all();

        $this->assertCount(
            2,
            $authors,
        );
        $this->assertSame(
            'Tolkien',
            $authors[0]->name,
        );
        $this->assertSame(
            'Nobody',
            $authors[1]->name,
        );
    }

    #[Test]
    public function where_has_on_has_one_returns_models_with_related_record(): void
    {
        $this->seed();

        $books = Book::select()
            ->whereHas(relation: 'isbn')
            ->all();

        $this->assertCount(2, $books);
        $this->assertSame('LOTR 1', $books[0]->title);
        $this->assertSame('Timeline Taxi', $books[1]->title);
    }

    #[Test]
    public function where_doesnt_have_on_has_one(): void
    {
        $this->seed();

        $books = Book::select()
            ->whereDoesntHave(relation: 'isbn')
            ->all();

        $this->assertCount(2, $books);
        $this->assertSame('LOTR 2', $books[0]->title);
        $this->assertSame('LOTR 3', $books[1]->title);
    }

    #[Test]
    public function where_has_on_belongs_to_many_returns_models_with_related_records(): void
    {
        $this->seed();

        $tags = Tag::select()
            ->whereHas(relation: 'books')
            ->all();

        $this->assertCount(1, $tags);
        $this->assertSame('fantasy', $tags[0]->label);
    }

    #[Test]
    public function where_doesnt_have_on_belongs_to_many(): void
    {
        $this->seed();

        $tags = Tag::select()
            ->whereDoesntHave(relation: 'books')
            ->all();

        $this->assertCount(1, $tags);
        $this->assertSame('orphan', $tags[0]->label);
    }

    #[Test]
    public function where_has_on_has_many_through_returns_models_with_related_records(): void
    {
        $this->seed();

        $tags = Tag::select()
            ->whereHas(relation: 'reviewers')
            ->all();

        $this->assertCount(1, $tags);
        $this->assertSame('fantasy', $tags[0]->label);
    }

    #[Test]
    public function where_doesnt_have_on_has_many_through(): void
    {
        $this->seed();

        $tags = Tag::select()
            ->whereDoesntHave(relation: 'reviewers')
            ->all();

        $this->assertCount(1, $tags);
        $this->assertSame('orphan', $tags[0]->label);
    }

    #[Test]
    public function where_has_on_has_one_through_returns_models_with_related_record(): void
    {
        $this->seed();

        $tags = Tag::select()
            ->whereHas(relation: 'topReviewer')
            ->all();

        $this->assertCount(1, $tags);
        $this->assertSame('fantasy', $tags[0]->label);
    }

    #[Test]
    public function count_with_where_has(): void
    {
        $this->seed();

        $count = Author::count()
            ->whereHas(relation: 'books')
            ->execute();

        $this->assertSame(
            2,
            $count,
        );
    }

    #[Test]
    public function count_with_where_has_compiles_correctly(): void
    {
        $sql = Author::count()
            ->whereHas(relation: 'books')
            ->compile();

        $this->assertSameWithoutBackticks(
            'SELECT COUNT(*) AS count FROM authors WHERE EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id)',
            $sql,
        );
    }

    #[Test]
    public function delete_with_where_has_compiles_correctly(): void
    {
        $sql = query(model: Author::class)
            ->delete()
            ->whereHas(relation: 'books')
            ->compile();

        $this->assertSameWithoutBackticks(
            'DELETE FROM authors WHERE EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id)',
            $sql,
        );
    }

    #[Test]
    public function delete_with_where_doesnt_have_removes_correct_records(): void
    {
        $this->seed();

        query(model: Author::class)
            ->delete()
            ->whereDoesntHave(relation: 'books')
            ->execute();

        $authors = Author::select()
            ->all();

        $this->assertCount(
            2,
            $authors,
        );
        $this->assertSame(
            'Brent',
            $authors[0]->name,
        );
        $this->assertSame(
            'Tolkien',
            $authors[1]->name,
        );
    }

    #[Test]
    public function update_with_where_has_compiles_correctly(): void
    {
        $sql = query(model: Author::class)
            ->update(name: 'Updated')
            ->whereHas(relation: 'books')
            ->compile();

        $this->assertSameWithoutBackticks(
            'UPDATE authors SET name = ? WHERE EXISTS (SELECT 1 FROM books WHERE books.author_id = authors.id)',
            $sql,
        );
    }

    #[Test]
    public function update_with_where_has_updates_correct_records(): void
    {
        $this->seed();

        query(model: Author::class)
            ->update(name: 'Has Books')
            ->whereHas(relation: 'books')
            ->execute();

        $authors = Author::select()
            ->orderBy(field: 'id')
            ->all();

        $this->assertSame(
            'Has Books',
            $authors[0]->name,
        );
        $this->assertSame(
            'Has Books',
            $authors[1]->name,
        );
        $this->assertSame(
            'Nobody',
            $authors[2]->name,
        );
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
            CreateTagTable::class,
            CreateBookTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $brent = Author::create(name: 'Brent');
        $tolkien = Author::create(name: 'Tolkien');
        Author::create(name: 'Nobody');

        $lotr1 = Book::create(title: 'LOTR 1', author: $tolkien);
        Book::create(title: 'LOTR 2', author: $tolkien);
        Book::create(title: 'LOTR 3', author: $tolkien);
        $timelineTaxi = Book::create(title: 'Timeline Taxi', author: $brent);

        Chapter::create(title: 'Chapter 1', book: $lotr1);
        Chapter::create(title: 'Chapter 2', book: $lotr1);

        Isbn::create(value: 'isbn-lotr-1', book: $lotr1);
        Isbn::create(value: 'isbn-tt', book: $timelineTaxi);

        $fantasy = Tag::create(label: 'fantasy');
        Tag::create(label: 'orphan');

        query(model: 'books_tags')
            ->insert(['book_id' => 1, 'tag_id' => 1])
            ->execute();

        $review = BookReview::create(content: 'Great book', tag: $fantasy);
        Reviewer::create(name: 'Alice', bookReview: $review);
    }
}
