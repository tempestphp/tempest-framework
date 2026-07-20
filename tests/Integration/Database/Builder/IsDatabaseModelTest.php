<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Carbon\Carbon;
use DateTime as NativeDateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsTo;
use Tempest\Database\Builder\QueryBuilders\QueryBuilder;
use Tempest\Database\Builder\QueryBuilders\SelectQueryBuilder;
use Tempest\Database\Exceptions\DeleteStatementWasInvalid;
use Tempest\Database\Exceptions\PrimaryKeyWasNotInitialized;
use Tempest\Database\Exceptions\PropertyWasNotARelation;
use Tempest\Database\Exceptions\RelationWasMissing;
use Tempest\Database\Exceptions\ValueWasMissing;
use Tempest\Database\HasMany;
use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\MigratesUp;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\QueryStatement;
use Tempest\Database\QueryStatements\CompoundStatement;
use Tempest\Database\QueryStatements\CreateEnumTypeStatement;
use Tempest\Database\QueryStatements\CreateTableStatement;
use Tempest\Database\QueryStatements\DropEnumTypeStatement;
use Tempest\Database\QueryStatements\PrimaryKeyStatement;
use Tempest\Database\QueryStatements\RawStatement;
use Tempest\Database\QueryStatements\TextStatement;
use Tempest\Database\Table;
use Tempest\DateTime\DateTime;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Validation\Rules\IsBetween;
use Tempest\Validation\SkipValidation;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookReviewTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTagTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreateIsbnTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Migrations\CreateReviewerTable;
use Tests\Tempest\Fixtures\Migrations\CreateTagTable;
use Tests\Tempest\Fixtures\Models\A;
use Tests\Tempest\Fixtures\Models\AWithEager;
use Tests\Tempest\Fixtures\Models\AWithLazy;
use Tests\Tempest\Fixtures\Models\AWithValue;
use Tests\Tempest\Fixtures\Models\AWithVirtual;
use Tests\Tempest\Fixtures\Models\B;
use Tests\Tempest\Fixtures\Models\C;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\BookReview;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Fixtures\Modules\Books\Models\Isbn;
use Tests\Tempest\Fixtures\Modules\Books\Models\Reviewer;
use Tests\Tempest\Fixtures\Modules\Books\Models\Tag;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\Mapper\map;

/**
 * @internal
 */
final class IsDatabaseModelTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function create_and_update_model(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo = Foo::create(
            bar: 'baz',
        );

        $this->assertSame('baz', $foo->bar);
        $this->assertInstanceOf(PrimaryKey::class, $foo->id);

        $foo = Foo::get($foo->id);

        $this->assertSame('baz', $foo->bar);
        $this->assertInstanceOf(PrimaryKey::class, $foo->id);

        $foo->update(
            bar: 'boo',
        );

        $foo = Foo::get($foo->id);

        $this->assertSame('boo', $foo->bar);
    }

    #[Test]
    public function get_with_non_id_object(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        Foo::create(
            bar: 'baz',
        );

        $foo = Foo::get(1);

        $this->assertSame(1, $foo->id->value);
    }

    #[Test]
    public function creating_many_and_saving_preserves_model_id(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $a = Foo::create(
            bar: 'a',
        );
        $b = Foo::create(
            bar: 'b',
        );

        $this->assertEquals(1, $a->id->value);
        $a->save();
        $this->assertEquals(1, $a->id->value);
    }

    #[Test]
    public function complex_query(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $book = Book::new(
            title: 'Book Title',
            author: new Author(
                name: 'Author Name',
                type: AuthorType::B,
            ),
        );

        $book = $book->save();

        $book = Book::get($book->id, relations: ['author']);

        $this->assertEquals(1, $book->id->value);
        $this->assertSame('Book Title', $book->title);
        $this->assertSame(AuthorType::B, $book->author->type);
        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertSame('Author Name', $book->author->name);
        $this->assertEquals(1, $book->author->id->value);
    }

    #[Test]
    public function all_with_relations(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(
            title: 'Book Title',
            author: new Author(
                name: 'Author Name',
                type: AuthorType::B,
            ),
        )->save();

        $books = Book::all(relations: [
            'author',
        ]);

        $this->assertCount(1, $books);

        $book = $books[0];

        $this->assertEquals(1, $book->id->value);
        $this->assertSame('Book Title', $book->title);
        $this->assertSame(AuthorType::B, $book->author->type);
        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertSame('Author Name', $book->author->name);
        $this->assertEquals(1, $book->author->id->value);
    }

    #[Test]
    public function create_with_hasone_relation(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateIsbnTable::class,
        );

        $book = Book::create(
            title: 'Book Title',
            isbn: Isbn::new(value: '123-456-789'),
        );

        $book = Book::findById($book->id)->load('isbn');

        $this->assertSame('123-456-789', $book->isbn->value);
    }

    #[Test]
    public function missing_relation_exception(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        new A(
            b: new B(
                c: new C(name: 'test'),
            ),
        )->save();

        $a = A::select()->first();

        $this->expectException(RelationWasMissing::class);

        $b = $a->b;
    }

    #[Test]
    public function missing_value_exception(): void
    {
        $a = map([])->to(AWithValue::class);

        $this->expectException(ValueWasMissing::class);

        $name = $a->name;
    }

    #[Test]
    public function nested_relations(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        new A(
            b: new B(
                c: new C(name: 'test'),
            ),
        )->save();

        $a = A::select()->with('b.c')->first();
        $this->assertSame('test', $a->b->c->name);

        $a = A::select()->with('b.c')->all()[0];
        $this->assertSame('test', $a->b->c->name);
    }

    #[Test]
    public function load_belongs_to(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        new A(
            b: new B(
                c: new C(name: 'test'),
            ),
        )->save();

        $a = A::select()->first();
        $this->assertFalse(isset($a->b));

        $a->load('b.c');
        $this->assertTrue(isset($a->b));
        $this->assertTrue(isset($a->b->c));
    }

    #[Test]
    public function has_many_relations(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::create(
            name: 'Author Name',
            type: AuthorType::B,
        );

        Book::create(
            title: 'Book Title',
            author: $author,
        );

        Book::create(
            title: 'Timeline Taxi',
            author: $author,
        );

        $author = Author::select()->with('books')->first();

        $this->assertCount(2, $author->books);
    }

    #[Test]
    public function query_has_many_returns_scoped_results(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $authorA = Author::create(
            name: 'Author A',
            type: AuthorType::A,
        );

        $authorB = Author::create(
            name: 'Author B',
            type: AuthorType::B,
        );

        Book::create(title: 'Book 1', author: $authorA);
        Book::create(title: 'Book 2', author: $authorA);
        Book::create(title: 'Book 3', author: $authorA);
        Book::create(title: 'Other Book', author: $authorB);

        $books = $authorA->query('books')->select()->all();

        $this->assertCount(3, $books);
        $this->assertContainsOnlyInstancesOf(Book::class, $books);
    }

    #[Test]
    public function query_has_many_supports_where(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::create(
            name: 'Author A',
            type: AuthorType::A,
        );

        Book::create(title: 'Alpha', author: $author);
        Book::create(title: 'Beta', author: $author);
        Book::create(title: 'Gamma', author: $author);

        $books = $author
            ->query('books')
            ->select()
            ->whereField(field: 'title', value: 'Beta')
            ->all();

        $this->assertCount(1, $books);
        $this->assertSame('Beta', $books[0]->title);
    }

    #[Test]
    public function query_has_many_supports_limit(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::create(
            name: 'Author A',
            type: AuthorType::A,
        );

        Book::create(title: 'Book 1', author: $author);
        Book::create(title: 'Book 2', author: $author);
        Book::create(title: 'Book 3', author: $author);

        $books = $author->query('books')->select()->limit(limit: 2)->all();

        $this->assertCount(2, $books);
    }

    #[Test]
    public function query_has_many_through_returns_scoped_results(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $tagA = Tag::create(label: 'fantasy');
        $tagB = Tag::create(label: 'sci-fi');

        $reviewA1 = BookReview::create(content: 'Great', tag: $tagA);
        $reviewA2 = BookReview::create(content: 'Good', tag: $tagA);
        $reviewB1 = BookReview::create(content: 'Meh', tag: $tagB);

        Reviewer::create(name: 'Alice', bookReview: $reviewA1);
        Reviewer::create(name: 'Bob', bookReview: $reviewA2);
        Reviewer::create(name: 'Charlie', bookReview: $reviewB1);

        $reviewers = $tagA->query('reviewers')->select()->all();

        $this->assertCount(2, $reviewers);
        $this->assertContainsOnlyInstancesOf(Reviewer::class, $reviewers);
    }

    #[Test]
    public function query_has_many_through_supports_where(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $tag = Tag::create(label: 'fantasy');

        $review1 = BookReview::create(content: 'Great', tag: $tag);
        $review2 = BookReview::create(content: 'Good', tag: $tag);

        Reviewer::create(name: 'Alice', bookReview: $review1);
        Reviewer::create(name: 'Bob', bookReview: $review2);

        $reviewers = $tag
            ->query('reviewers')
            ->select()
            ->whereField(field: 'name', value: 'Alice')
            ->all();

        $this->assertCount(1, $reviewers);
        $this->assertSame('Alice', $reviewers[0]->name);
    }

    #[Test]
    public function query_belongs_to_many_returns_scoped_results(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateTagTable::class,
            CreateBookTagTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $book1 = Book::create(title: 'Book 1', author: $author);
        $book2 = Book::create(title: 'Book 2', author: $author);
        $book3 = Book::create(title: 'Book 3', author: $author);

        $tagA = Tag::create(label: 'fantasy');
        $tagB = Tag::create(label: 'sci-fi');

        query(model: 'books_tags')->insert(['book_id' => $book1->id->value, 'tag_id' => $tagA->id->value])->execute();
        query(model: 'books_tags')->insert(['book_id' => $book2->id->value, 'tag_id' => $tagA->id->value])->execute();
        query(model: 'books_tags')->insert(['book_id' => $book3->id->value, 'tag_id' => $tagB->id->value])->execute();

        $books = $tagA->query('books')->select()->all();

        $this->assertCount(2, $books);
        $this->assertContainsOnlyInstancesOf(Book::class, $books);
    }

    #[Test]
    public function query_belongs_to_many_supports_where(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateTagTable::class,
            CreateBookTagTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $book1 = Book::create(title: 'Alpha', author: $author);
        $book2 = Book::create(title: 'Beta', author: $author);

        $tag = Tag::create(label: 'fantasy');

        query(model: 'books_tags')->insert(['book_id' => $book1->id->value, 'tag_id' => $tag->id->value])->execute();
        query(model: 'books_tags')->insert(['book_id' => $book2->id->value, 'tag_id' => $tag->id->value])->execute();

        $books = $tag
            ->query('books')
            ->select()
            ->whereField(field: 'title', value: 'Alpha')
            ->all();

        $this->assertCount(1, $books);
        $this->assertSame('Alpha', $books[0]->title);
    }

    #[Test]
    public function query_has_many_with_explicit_attribute(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTestUserMigration::class,
            CreateTestPostMigration::class,
        );

        $user = TestUser::create(name: 'Alice');

        query(model: 'test_posts')
            ->insert(['title' => 'Post 1', 'body' => 'Body 1', 'test_user_id' => $user->id->value])
            ->execute();
        query(model: 'test_posts')
            ->insert(['title' => 'Post 2', 'body' => 'Body 2', 'test_user_id' => $user->id->value])
            ->execute();

        $posts = $user->query('posts')->select()->all();

        $this->assertCount(2, $posts);
        $this->assertContainsOnlyInstancesOf(TestPost::class, $posts);
    }

    #[Test]
    public function query_belongs_to_select(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::create(name: 'Target Author', type: AuthorType::A);
        $book = Book::create(title: 'Test', author: $author);

        $result = $book->query('author')->select()->first();

        $this->assertInstanceOf(Author::class, $result);
        $this->assertSame('Target Author', $result->name);
    }

    #[Test]
    public function query_belongs_to_count(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $book = Book::create(title: 'Test', author: $author);

        $this->assertSame(1, $book->query('author')->count()->execute());
    }

    #[Test]
    public function query_has_one_select(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateIsbnTable::class,
        );

        $book = Book::create(title: 'Test Book');
        Isbn::new(value: '978-123', book: $book)->save();

        $result = $book->query('isbn')->select()->first();

        $this->assertInstanceOf(Isbn::class, $result);
        $this->assertSame('978-123', $result->value);
    }

    #[Test]
    public function query_has_one_count(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateIsbnTable::class,
        );

        $book = Book::create(title: 'Test Book');
        Isbn::new(value: '978-123', book: $book)->save();

        $this->assertSame(1, $book->query('isbn')->count()->execute());
    }

    #[Test]
    public function query_has_one_update(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateIsbnTable::class,
        );

        $bookA = Book::create(title: 'Book A');
        $bookB = Book::create(title: 'Book B');
        Isbn::new(value: 'old-isbn', book: $bookA)->save();
        Isbn::new(value: 'keep-isbn', book: $bookB)->save();

        $bookA->query('isbn')->update(value: 'new-isbn')->execute();

        $isbnA = $bookA->query('isbn')->select()->first();
        $isbnB = $bookB->query('isbn')->select()->first();

        $this->assertSame('new-isbn', $isbnA->value);
        $this->assertSame('keep-isbn', $isbnB->value);
    }

    #[Test]
    public function query_has_one_delete(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateIsbnTable::class,
        );

        $bookA = Book::create(title: 'Book A');
        $bookB = Book::create(title: 'Book B');
        Isbn::new(value: 'isbn-a', book: $bookA)->save();
        Isbn::new(value: 'isbn-b', book: $bookB)->save();

        $bookA->query('isbn')->delete()->execute();

        $this->assertSame(0, $bookA->query('isbn')->count()->execute());
        $this->assertSame(1, $bookB->query('isbn')->count()->execute());
    }

    #[Test]
    public function query_has_one_through_select(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $tag = Tag::create(label: 'fantasy');
        $review = BookReview::create(content: 'Great', tag: $tag);
        Reviewer::create(name: 'Alice', bookReview: $review);

        $result = $tag->query('topReviewer')->select()->first();

        $this->assertInstanceOf(Reviewer::class, $result);
        $this->assertSame('Alice', $result->name);
    }

    #[Test]
    public function query_has_one_through_count(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $tag = Tag::create(label: 'fantasy');
        $review = BookReview::create(content: 'Great', tag: $tag);
        Reviewer::create(name: 'Alice', bookReview: $review);

        $this->assertSame(1, $tag->query('topReviewer')->count()->execute());
    }

    #[Test]
    public function query_has_one_through_update(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $tagA = Tag::create(label: 'fantasy');
        $tagB = Tag::create(label: 'sci-fi');
        $reviewA = BookReview::create(content: 'Great', tag: $tagA);
        $reviewB = BookReview::create(content: 'Meh', tag: $tagB);
        Reviewer::create(name: 'Alice', bookReview: $reviewA);
        Reviewer::create(name: 'Bob', bookReview: $reviewB);

        $tagA->query('topReviewer')->update(name: 'Updated')->execute();

        $resultA = $tagA->query('topReviewer')->select()->first();
        $resultB = $tagB->query('topReviewer')->select()->first();

        $this->assertSame('Updated', $resultA->name);
        $this->assertSame('Bob', $resultB->name);
    }

    #[Test]
    public function query_has_one_through_delete(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $tagA = Tag::create(label: 'fantasy');
        $tagB = Tag::create(label: 'sci-fi');
        $reviewA = BookReview::create(content: 'Great', tag: $tagA);
        $reviewB = BookReview::create(content: 'Meh', tag: $tagB);
        Reviewer::create(name: 'Alice', bookReview: $reviewA);
        Reviewer::create(name: 'Bob', bookReview: $reviewB);

        $tagA->query('topReviewer')->delete()->execute();

        $this->assertSame(0, $tagA->query('topReviewer')->count()->execute());
        $this->assertSame(1, $tagB->query('topReviewer')->count()->execute());
    }

    #[Test]
    public function query_has_many_with_where_has(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $bookWithChapters = Book::create(title: 'With Chapters', author: $author);
        Book::create(title: 'No Chapters', author: $author);

        Chapter::new(title: 'Chapter 1', contents: 'Content', book: $bookWithChapters)->save();

        $books = $author
            ->query('books')
            ->select()
            ->whereHas(relation: 'chapters')
            ->all();

        $this->assertCount(1, $books);
        $this->assertSame('With Chapters', $books[0]->title);
    }

    #[Test]
    public function query_has_many_with_where_doesnt_have_and_where_field(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $bookA = Book::create(title: 'Alpha', author: $author);
        Book::create(title: 'Beta', author: $author);
        Book::create(title: 'Gamma', author: $author);

        Chapter::new(title: 'Ch 1', contents: 'Content', book: $bookA)->save();

        $books = $author
            ->query('books')
            ->select()
            ->whereDoesntHave(relation: 'chapters')
            ->whereField(field: 'title', value: 'Beta')
            ->all();

        $this->assertCount(1, $books);
        $this->assertSame('Beta', $books[0]->title);
    }

    #[Test]
    public function query_has_many_with_where_has_callback(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $bookA = Book::create(title: 'Book A', author: $author);
        $bookB = Book::create(title: 'Book B', author: $author);

        Chapter::new(title: 'Intro', contents: 'Content', book: $bookA)->save();
        Chapter::new(title: 'Advanced Topics', contents: 'Content', book: $bookB)->save();

        $books = $author
            ->query('books')
            ->select()
            ->whereHas(relation: 'chapters', callback: function (SelectQueryBuilder $q): void {
                $q->whereField(field: 'title', value: 'Advanced Topics');
            })
            ->all();

        $this->assertCount(1, $books);
        $this->assertSame('Book B', $books[0]->title);
    }

    #[Test]
    public function query_has_many_with_where_doesnt_have_callback(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $bookA = Book::create(title: 'Book A', author: $author);
        Book::create(title: 'Book B', author: $author);

        Chapter::new(title: 'Draft', contents: 'WIP', book: $bookA)->save();

        $books = $author
            ->query('books')
            ->select()
            ->whereDoesntHave(relation: 'chapters', callback: function (SelectQueryBuilder $q): void {
                $q->whereField(field: 'title', value: 'Draft');
            })
            ->all();

        $this->assertCount(1, $books);
        $this->assertSame('Book B', $books[0]->title);
    }

    #[Test]
    public function query_throws_for_nonexistent_property(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);

        $this->expectException(PropertyWasNotARelation::class);

        $author->query('nonexistent');
    }

    #[Test]
    public function query_throws_for_unsaved_model(): void
    {
        $author = new Author(name: 'Unsaved');

        $this->expectException(PrimaryKeyWasNotInitialized::class);

        $author->query('books');
    }

    #[Test]
    public function query_has_many_returns_empty_for_no_results(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);

        $books = $author->query('books')->select()->all();

        $this->assertCount(0, $books);
    }

    #[Test]
    public function query_has_many_count(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $authorA = Author::create(name: 'Author A', type: AuthorType::A);
        $authorB = Author::create(name: 'Author B', type: AuthorType::B);

        Book::create(title: 'Book 1', author: $authorA);
        Book::create(title: 'Book 2', author: $authorA);
        Book::create(title: 'Book 3', author: $authorA);
        Book::create(title: 'Other', author: $authorB);

        $count = $authorA->query('books')->count()->execute();

        $this->assertSame(3, $count);
    }

    #[Test]
    public function query_has_many_update(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $authorA = Author::create(name: 'Author A', type: AuthorType::A);
        $authorB = Author::create(name: 'Author B', type: AuthorType::B);

        Book::create(title: 'Old Title 1', author: $authorA);
        Book::create(title: 'Old Title 2', author: $authorA);
        Book::create(title: 'Keep This', author: $authorB);

        $authorA->query('books')->update(title: 'Updated')->execute();

        $booksA = $authorA->query('books')->select()->all();
        $booksB = $authorB->query('books')->select()->all();

        $this->assertSame('Updated', $booksA[0]->title);
        $this->assertSame('Updated', $booksA[1]->title);
        $this->assertSame('Keep This', $booksB[0]->title);
    }

    #[Test]
    public function query_has_many_delete(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        $authorA = Author::create(name: 'Author A', type: AuthorType::A);
        $authorB = Author::create(name: 'Author B', type: AuthorType::B);

        Book::create(title: 'Book 1', author: $authorA);
        Book::create(title: 'Book 2', author: $authorA);
        Book::create(title: 'Keep This', author: $authorB);

        $authorA->query('books')->delete()->execute();

        $this->assertSame(0, $authorA->query('books')->count()->execute());
        $this->assertSame(1, $authorB->query('books')->count()->execute());
    }

    #[Test]
    public function query_has_many_through_count(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $tagA = Tag::create(label: 'fantasy');
        $tagB = Tag::create(label: 'sci-fi');

        $reviewA1 = BookReview::create(content: 'Great', tag: $tagA);
        $reviewA2 = BookReview::create(content: 'Good', tag: $tagA);
        $reviewB1 = BookReview::create(content: 'Meh', tag: $tagB);

        Reviewer::create(name: 'Alice', bookReview: $reviewA1);
        Reviewer::create(name: 'Bob', bookReview: $reviewA2);
        Reviewer::create(name: 'Charlie', bookReview: $reviewB1);

        $this->assertSame(2, $tagA->query('reviewers')->count()->execute());
        $this->assertSame(1, $tagB->query('reviewers')->count()->execute());
    }

    #[Test]
    public function query_belongs_to_many_count(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateTagTable::class,
            CreateBookTagTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $book1 = Book::create(title: 'Book 1', author: $author);
        $book2 = Book::create(title: 'Book 2', author: $author);

        $tag = Tag::create(label: 'fantasy');

        query(model: 'books_tags')->insert(['book_id' => $book1->id->value, 'tag_id' => $tag->id->value])->execute();
        query(model: 'books_tags')->insert(['book_id' => $book2->id->value, 'tag_id' => $tag->id->value])->execute();

        $this->assertSame(2, $tag->query('books')->count()->execute());
    }

    #[Test]
    public function query_has_many_through_update(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $tagA = Tag::create(label: 'fantasy');
        $tagB = Tag::create(label: 'sci-fi');

        $reviewA = BookReview::create(content: 'Great', tag: $tagA);
        $reviewB = BookReview::create(content: 'Meh', tag: $tagB);

        Reviewer::create(name: 'Alice', bookReview: $reviewA);
        Reviewer::create(name: 'Bob', bookReview: $reviewB);

        $tagA->query('reviewers')->update(name: 'Updated')->execute();

        $reviewersA = $tagA->query('reviewers')->select()->all();
        $reviewersB = $tagB->query('reviewers')->select()->all();

        $this->assertSame('Updated', $reviewersA[0]->name);
        $this->assertSame('Bob', $reviewersB[0]->name);
    }

    #[Test]
    public function query_has_many_through_delete(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateTagTable::class,
            CreateBookReviewTable::class,
            CreateReviewerTable::class,
        );

        $tagA = Tag::create(label: 'fantasy');
        $tagB = Tag::create(label: 'sci-fi');

        $reviewA = BookReview::create(content: 'Great', tag: $tagA);
        $reviewB = BookReview::create(content: 'Meh', tag: $tagB);

        Reviewer::create(name: 'Alice', bookReview: $reviewA);
        Reviewer::create(name: 'Bob', bookReview: $reviewB);

        $tagA->query('reviewers')->delete()->execute();

        $this->assertSame(0, $tagA->query('reviewers')->count()->execute());
        $this->assertSame(1, $tagB->query('reviewers')->count()->execute());
    }

    #[Test]
    public function query_belongs_to_many_delete(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateTagTable::class,
            CreateBookTagTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $book1 = Book::create(title: 'Book 1', author: $author);
        $book2 = Book::create(title: 'Book 2', author: $author);
        $book3 = Book::create(title: 'Book 3', author: $author);

        $tagA = Tag::create(label: 'fantasy');
        $tagB = Tag::create(label: 'sci-fi');

        query(model: 'books_tags')->insert(['book_id' => $book1->id->value, 'tag_id' => $tagA->id->value])->execute();
        query(model: 'books_tags')->insert(['book_id' => $book2->id->value, 'tag_id' => $tagA->id->value])->execute();
        query(model: 'books_tags')->insert(['book_id' => $book3->id->value, 'tag_id' => $tagB->id->value])->execute();

        $tagA->query('books')->delete()->execute();

        $this->assertSame(0, $tagA->query('books')->count()->execute());
        $this->assertSame(1, $tagB->query('books')->count()->execute());
    }

    #[Test]
    public function query_belongs_to_many_update(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateTagTable::class,
            CreateBookTagTable::class,
        );

        $author = Author::create(name: 'Author', type: AuthorType::A);
        $book1 = Book::create(title: 'Old 1', author: $author);
        $book2 = Book::create(title: 'Old 2', author: $author);
        $book3 = Book::create(title: 'Keep', author: $author);

        $tagA = Tag::create(label: 'fantasy');
        $tagB = Tag::create(label: 'sci-fi');

        query(model: 'books_tags')->insert(['book_id' => $book1->id->value, 'tag_id' => $tagA->id->value])->execute();
        query(model: 'books_tags')->insert(['book_id' => $book2->id->value, 'tag_id' => $tagA->id->value])->execute();
        query(model: 'books_tags')->insert(['book_id' => $book3->id->value, 'tag_id' => $tagB->id->value])->execute();

        $tagA->query('books')->update(title: 'Updated')->execute();

        $booksA = $tagA->query('books')->select()->all();
        $booksB = $tagB->query('books')->select()->all();

        $this->assertSame('Updated', $booksA[0]->title);
        $this->assertSame('Updated', $booksA[1]->title);
        $this->assertSame('Keep', $booksB[0]->title);
    }

    #[Test]
    public function has_many_through_relation(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateHasManyParentTable::class,
            CreateHasManyChildTable::class,
            CreateHasManyThroughTable::class,
        );

        $parent = new ParentModel(name: 'parent')->save();

        $childA = new ChildModel(name: 'A')->save();
        $childB = new ChildModel(name: 'B')->save();

        new ThroughModel(parent: $parent, child: $childA)->save();
        new ThroughModel(parent: $parent, child: $childB)->save();

        $parent = ParentModel::get($parent->id, ['through.child']);

        $this->assertSame('A', $parent->through[0]->child->name);
        $this->assertSame('B', $parent->through[1]->child->name);
    }

    #[Test]
    public function empty_has_many_relation(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateHasManyChildTable::class,
        );

        Book::new(title: 'Timeline Taxi')->save();
        $book = Book::select()->with('chapters')->first();
        $this->assertEmpty($book->chapters);
    }

    #[Test]
    public function has_one_relation(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateChapterTable::class,
            CreateHasManyChildTable::class,
            CreateIsbnTable::class,
        );

        $book = Book::new(title: 'Timeline Taxi')->save();
        $isbn = Isbn::new(value: 'tt-1', book: $book)->save();

        $isbn = Isbn::select()->with('book')->get($isbn->id);

        $this->assertSame('Timeline Taxi', $isbn->book->title);
    }

    #[Test]
    public function invalid_has_one_relation(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateHasManyParentTable::class,
            CreateHasManyChildTable::class,
            CreateHasManyThroughTable::class,
        );

        $parent = new ParentModel(name: 'parent')->save();

        $childA = new ChildModel(name: 'A')->save();
        $childB = new ChildModel(name: 'B')->save();

        new ThroughModel(parent: $parent, child: $childA, child2: $childB)->save();

        $child = ChildModel::get($childA->id, ['through.parent']);
        $this->assertSame('parent', $child->through->parent->name);

        $child2 = ChildModel::select()->with('through2.parent')->get($childB->id);
        $this->assertSame('parent', $child2->through2->parent->name);
    }

    #[Test]
    public function lazy_load(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        new AWithLazy(
            b: new B(
                c: new C(name: 'test'),
            ),
        )->save();

        $a = AWithLazy::select()->first();

        $this->assertFalse(isset($a->b));

        /** @phpstan-ignore expr.resultUnused */
        $a->b; // The side effect from accessing ->b will cause it to load

        $this->assertTrue(isset($a->b));
    }

    #[Test]
    public function eager_load(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        new AWithLazy(
            b: new B(
                c: new C(name: 'test'),
            ),
        )->save();

        $a = AWithEager::select()->first();
        $this->assertTrue(isset($a->b));
        $this->assertTrue(isset($a->b->c));
    }

    #[Test]
    public function no_result(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        $this->assertNull(A::select()->first());
    }

    #[Test]
    public function create_with_virtual_property(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        $a = AWithVirtual::create(
            b: new B(
                c: new C(name: 'test'),
            ),
        );

        $this->assertSame(-$a->id->value, $a->fake);
    }

    #[Test]
    public function virtual_hooked_property(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateModelWithHookedVirtualPropertyTable::class,
        );

        $a = ModelWithHookedVirtualProperty::create(
            name: 'a',
        );

        $this->assertSame('A', $a->hookedName);

        $a = ModelWithHookedVirtualProperty::select()->first();
        $this->assertSame('A', $a->hookedName);

        $a->name = 'b';
        $a->save();
        $this->assertSame('B', $a->hookedName);
    }

    #[Test]
    public function select_virtual_property(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        new A(
            b: new B(
                c: new C(name: 'test'),
            ),
        )->save();

        $a = AWithVirtual::select()->first();

        $this->assertSame(-$a->id->value, $a->fake);
    }

    #[Test]
    public function update_with_virtual_property(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        $a = AWithVirtual::create(
            b: new B(
                c: new C(name: 'test'),
            ),
        );

        $a->update(
            b: new B(
                c: new C(name: 'updated'),
            ),
        );

        $updatedA = AWithVirtual::select()
            ->with('b.c')
            ->where('id', $a->id)
            ->first();

        $this->assertSame(-$updatedA->id->value, $updatedA->fake);
        $this->assertSame('updated', $updatedA->b->c->name);
    }

    #[Test]
    public function update_or_create(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
        );

        Book::new(
            title: 'A',
            author: new Author(
                name: 'Author Name',
                type: AuthorType::B,
            ),
        )->save();

        Book::updateOrCreate(
            ['title' => 'A'],
            ['title' => 'B'],
        );

        $this->assertNull(Book::select()->where('title', 'A')->first());
        $this->assertNotNull(Book::select()->where('title', 'B')->first());
    }

    #[Test]
    public function update_or_create_uses_initial_data_to_create(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
        );

        Author::updateOrCreate(
            find: ['name' => 'Brent'],
            update: ['type' => AuthorType::B],
        );

        $this->assertNotNull(
            Author::select()
                ->where('name', 'Brent')
                ->where('type', AuthorType::B)
                ->first(),
        );
    }

    #[Test]
    public function delete(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo = Foo::create(
            bar: 'baz',
        );

        $bar = Foo::create(
            bar: 'baz',
        );

        $foo->delete();

        $this->assertNull(Foo::get($foo->id));
        $this->assertNotNull(Foo::get($bar->id));
    }

    #[Test]
    public function delete_via_model_class_with_where_conditions(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo1 = Foo::create(bar: 'delete_me');
        $foo2 = Foo::create(bar: 'keep_me');
        $foo3 = Foo::create(bar: 'delete_me');

        query(Foo::class)
            ->delete()
            ->where('bar', 'delete_me')
            ->execute();

        $this->assertNull(Foo::get($foo1->id));
        $this->assertNotNull(Foo::get($foo2->id));
        $this->assertNull(Foo::get($foo3->id));
    }

    #[Test]
    public function delete_via_model_instance_with_primary_key(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo1 = Foo::create(bar: 'first');
        $foo2 = Foo::create(bar: 'second');
        $foo1->delete();

        $this->assertNull(Foo::get($foo1->id));
        $this->assertNotNull(Foo::get($foo2->id));
        $this->assertSame('second', Foo::get($foo2->id)->bar);
    }

    #[Test]
    public function delete_with_uninitialized_primary_key(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo = new Foo();
        $foo->bar = 'unsaved';

        $this->expectException(DeleteStatementWasInvalid::class);
        $foo->delete();
    }

    #[Test]
    public function delete_nonexistent_record(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo = Foo::create(bar: 'test');
        $fooId = $foo->id;

        // Delete the record
        $foo->delete();

        // Delete again
        $foo->delete();

        $this->assertNull(Foo::get($fooId));
    }

    #[Test]
    public function nullable_relations(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateBNullableTable::class,
            CreateANullableTable::class,
        );

        $a = ANullableModel::create(
            name: 'a',
        );

        $a->load('b');

        $this->assertNull($a->b);
    }

    #[Test]
    public function nullable_relation_save(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateBNullableTable::class,
            CreateANullableTable::class,
        );

        ANullableModel::create(
            name: 'a',
            b: BNullableModel::new(
                name: 'b',
            ),
        );

        $a = ANullableModel::select()->first();
        $a->save();

        $a = ANullableModel::select()->with('b')->first();

        $this->assertNotNull($a->b);
        $this->assertSame('b', $a->b->name);
    }

    #[Test]
    public function on_returns_query_builder_with_database_tag(): void
    {
        $builder = Foo::on('analytics');

        $this->assertInstanceOf(QueryBuilder::class, $builder);
        $this->assertSame('analytics', $builder->onDatabase);
    }

    #[Test]
    public function on_propagates_tag_to_select_builder(): void
    {
        $selectBuilder = Foo::on('analytics')->select();

        $this->assertSame('analytics', $selectBuilder->onDatabase);
    }

    #[Test]
    public function on_propagates_tag_to_insert_builder(): void
    {
        $insertBuilder = Foo::on('analytics')->insert();

        $this->assertSame('analytics', $insertBuilder->onDatabase);
    }

    #[Test]
    public function on_propagates_tag_to_count_builder(): void
    {
        $countBuilder = Foo::on('analytics')->count();

        $this->assertSame('analytics', $countBuilder->onDatabase);
    }

    #[Test]
    public function on_propagates_tag_to_delete_builder(): void
    {
        $deleteBuilder = Foo::on('analytics')->delete();

        $this->assertSame('analytics', $deleteBuilder->onDatabase);
    }

    #[Test]
    public function on_with_null_sets_null_tag(): void
    {
        $builder = Foo::on(null);

        $this->assertNull($builder->onDatabase);
    }

    #[Test]
    public function on_with_enum_tag(): void
    {
        $builder = Foo::on(TestDatabaseTag::Analytics);

        $this->assertSame(TestDatabaseTag::Analytics, $builder->onDatabase);
    }

    #[Test]
    public function on_database_returns_clone(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo = Foo::create(bar: 'baz');
        $clone = $foo->onDatabase('analytics');

        $this->assertNotSame($foo, $clone);
    }

    #[Test]
    public function on_database_does_not_mutate_original(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo = Foo::create(bar: 'baz');
        $foo->onDatabase('analytics');

        // Original still works against default database
        $foo->update(bar: 'updated');

        $refreshed = Foo::get($foo->id);

        $this->assertSame('updated', $refreshed->bar);
    }

    #[Test]
    public function on_database_preserves_model_data(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo = Foo::create(bar: 'baz');
        $clone = $foo->onDatabase('analytics');

        $this->assertSame('baz', $clone->bar);
        $this->assertEquals($foo->id, $clone->id);
    }
}

final class Foo
{
    use IsDatabaseModel;

    public string $bar;
}

final class FooDatabaseMigration implements MigratesUp
{
    private(set) string $name = 'foos';

    public function up(): QueryStatement
    {
        return new CreateTableStatement(
            tableName: 'foos',
            statements: [
                new PrimaryKeyStatement(),
                new TextStatement('bar'),
            ],
        );
    }
}

final class CreateATable implements MigratesUp
{
    private(set) string $name = '100-create-a';

    public function up(): QueryStatement
    {
        return new CreateTableStatement(
            'a',
            [
                new PrimaryKeyStatement(),
                new RawStatement('b_id INTEGER'),
            ],
        );
    }
}

final class CreateBTable implements MigratesUp
{
    private(set) string $name = '100-create-b';

    public function up(): QueryStatement
    {
        return new CreateTableStatement(
            'b',
            [
                new PrimaryKeyStatement(),
                new RawStatement('c_id INTEGER'),
            ],
        );
    }
}

final class CreateCTable implements MigratesUp
{
    private(set) string $name = '100-create-c';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('c', [
            new PrimaryKeyStatement(),
            new TextStatement('name'),
        ]);
    }
}

final class CreateCarbonModelTable implements MigratesUp
{
    public string $name = '2024-12-17_create_users_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(CarbonModel::class)
            ->primary()
            ->datetime('createdAt');
    }
}

final class CreateCasterModelTable implements MigratesUp
{
    public string $name = '0000_create_caster_model_table';

    public function up(): QueryStatement
    {
        return new CompoundStatement(
            new DropEnumTypeStatement(CasterEnum::class),
            new CreateEnumTypeStatement(CasterEnum::class),
            CreateTableStatement::forModel(CasterModel::class)
                ->primary()
                ->datetime('date')
                ->array('array_prop')
                ->enum('enum_prop', CasterEnum::class),
        );
    }
}

final class CreateDateTimeModelTable implements MigratesUp
{
    public string $name = '0001_datetime_model_table';

    public function up(): QueryStatement
    {
        return CreateTableStatement::forModel(DateTimeModel::class)
            ->primary()
            ->datetime('phpDateTime')
            ->datetime('tempestDateTime');
    }
}

final class CreateHasManyChildTable implements MigratesUp
{
    private(set) string $name = '100-create-has-many-child';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('child')
            ->primary()
            ->varchar('name');
    }
}

final class CreateHasManyParentTable implements MigratesUp
{
    private(set) string $name = '100-create-has-many-parent';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('parent')
            ->primary()
            ->varchar('name');
    }
}

final class CreateHasManyThroughTable implements MigratesUp
{
    private(set) string $name = '100-create-has-many-through';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('through')
            ->primary()
            ->belongsTo('through.parent_id', 'parent.id')
            ->belongsTo('through.child_id', 'child.id')
            ->belongsTo('through.child2_id', 'child.id', nullable: true);
    }
}

#[Table('custom_attribute_table_name')]
final class AttributeTableNameModel
{
    use IsDatabaseModel;
}

final class BaseModel
{
    use IsDatabaseModel;
}

final readonly class CarbonCaster implements Caster
{
    public static function for(): false
    {
        return false;
    }

    public function cast(mixed $input): mixed
    {
        return new Carbon($input);
    }
}

final class CarbonModel
{
    use IsDatabaseModel;

    public function __construct(
        public Carbon $createdAt,
    ) {}
}

final readonly class CarbonSerializer implements Serializer
{
    public static function for(): false
    {
        return false;
    }

    public function serialize(mixed $input): string
    {
        if (! $input instanceof Carbon) {
            throw new ValueCouldNotBeSerialized(Carbon::class);
        }

        return $input->format('Y-m-d H:i:s');
    }
}

enum CasterEnum: string
{
    case FOO = 'foo';
    case BAR = 'bar';
}

final class CasterModel
{
    use IsDatabaseModel;

    public function __construct(
        public DateTimeImmutable $date,
        public array $array_prop,
        public CasterEnum $enum_prop,
    ) {}
}

#[Table('child')]
final class ChildModel
{
    use IsDatabaseModel;

    #[HasOne]
    public ThroughModel $through;

    #[HasOne(ownerJoin: 'child2_id')]
    public ThroughModel $through2;

    public function __construct(
        public string $name,
    ) {}
}

final class DateTimeModel
{
    use IsDatabaseModel;

    public function __construct(
        public PrimaryKey $id,
        public NativeDateTime $phpDateTime,
        public DateTime $tempestDateTime,
    ) {}
}

final class ModelWithValidation
{
    use IsDatabaseModel;

    #[IsBetween(min: 1, max: 10)]
    public int $index;

    #[SkipValidation]
    public int $skip;
}

#[Table('parent')]
final class ParentModel
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,

        /** @var \Tests\Tempest\Integration\Database\Builder\ThroughModel[] */
        public array $through = [],
    ) {}
}

#[Table('custom_static_method_table_name')]
final class StaticMethodTableNameModel
{
    use IsDatabaseModel;
}

#[Table('through')]
final class ThroughModel
{
    use IsDatabaseModel;

    public function __construct(
        public ParentModel $parent,
        public ChildModel $child,
        #[BelongsTo(ownerJoin: 'child2_id')]
        public ?ChildModel $child2 = null,
    ) {}
}

final class TestUser
{
    use IsDatabaseModel;

    /** @var \Tests\Tempest\Integration\Database\Builder\TestPost[] */
    #[HasMany]
    public array $posts = [];

    public function __construct(
        public string $name,
    ) {}
}

final class TestPost
{
    use IsDatabaseModel;

    public function __construct(
        public string $title,
        public string $body,
    ) {}
}

final class CreateTestUserMigration implements MigratesUp
{
    public string $name = '010_create_test_users';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('test_users')
            ->primary()
            ->text('name');
    }
}

final class CreateTestPostMigration implements MigratesUp
{
    public string $name = '011_create_test_posts';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('test_posts')
            ->primary()
            ->foreignId('test_user_id', constrainedOn: 'test_users')
            ->string('title')
            ->text('body');
    }
}

final class ModelWithoutPrimaryKey
{
    public function __construct(
        public string $name,
        public string $description,
    ) {}
}

final class CreateModelWithoutPrimaryKeyMigration implements MigratesUp
{
    private(set) string $name = '100-create-model-without-primary-key';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('model_without_primary_keys')
            ->text('name')
            ->text('description');
    }
}

final class CreateANullableTable implements MigratesUp
{
    private(set) string $name = '100-create-a-nullable';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('a')
            ->primary()
            ->string('name')
            ->belongsTo('a.b_id', 'b.id', nullable: true);
    }
}

final class CreateBNullableTable implements MigratesUp
{
    private(set) string $name = '100-create-b-nullable';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('b')
            ->primary()
            ->string('name');
    }
}

#[Table('a')]
final class ANullableModel
{
    use IsDatabaseModel;

    public ?BNullableModel $b = null;

    public string $name;
}

#[Table('b')]
final class BNullableModel
{
    use IsDatabaseModel;

    public string $name;
}

final class CreateModelWithHookedVirtualPropertyTable implements MigratesUp
{
    public string $name = '100-create-model-with-hooked-virtual-property';

    public function up(): QueryStatement
    {
        return new CreateTableStatement('model_with_hooked_virtual_property')
            ->primary()
            ->string('name');
    }
}

#[Table('model_with_hooked_virtual_property')]
final class ModelWithHookedVirtualProperty
{
    use IsDatabaseModel;

    public string $name;

    public string $hookedName {
        get => strtoupper($this->name);
    }
}

enum TestDatabaseTag
{
    case Analytics;
    case Reporting;
}
