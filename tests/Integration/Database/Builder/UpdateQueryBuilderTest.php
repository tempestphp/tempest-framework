<?php

namespace Tests\Tempest\Integration\Database\Builder;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\Builder\QueryBuilders\UpdateQueryBuilder;
use Tempest\Database\Config\DatabaseDialect;
use Tempest\Database\Database;
use Tempest\Database\Exceptions\CouldNotUpdateRelation;
use Tempest\Database\Exceptions\UpdateStatementWasInvalid;
use Tempest\Database\HasMany;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\PrimaryKey;
use Tempest\Database\Query;
use Tempest\Database\Table;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTagTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Migrations\CreateTagTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Chapter;
use Tests\Tempest\Fixtures\Modules\Books\Models\Tag;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

final class UpdateQueryBuilderTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function update_on_plain_table(): void
    {
        $query = query('chapters')
            ->update(
                title: 'Chapter 01',
                index: 1,
            )
            ->whereRaw('`id` = ?', 10)
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `chapters` SET `title` = ?, `index` = ? WHERE `id` = ?',
            $query->compile(),
        );

        $this->assertSame(
            ['Chapter 01', 1, 10],
            $query->bindings,
        );
    }

    #[Test]
    public function global_update(): void
    {
        $query = query('chapters')
            ->update(index: 0)
            ->allowAll()
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `chapters` SET `index` = ?',
            $query->compile(),
        );

        $this->assertSame(
            [0],
            $query->bindings,
        );
    }

    #[Test]
    public function global_update_fails_without_allow_all(): void
    {
        $this->expectException(UpdateStatementWasInvalid::class);

        query('chapters')
            ->update(index: 0)
            ->build()
            ->compile();
    }

    #[Test]
    public function model_update_with_values(): void
    {
        $query = query(Book::class)
            ->update(
                title: 'Chapter 02',
            )
            ->whereRaw('`id` = ?', 10)
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `books` SET `title` = ? WHERE `id` = ?',
            $query->compile(),
        );

        $this->assertSame(
            ['Chapter 02', 10],
            $query->bindings,
        );
    }

    #[Test]
    public function model_update_with_object(): void
    {
        $book = Book::new(
            id: new PrimaryKey(10),
            title: 'Chapter 01',
        );

        $query = query($book)
            ->update(
                title: 'Chapter 02',
            )
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `books` SET `title` = ? WHERE `books`.`id` = ?',
            $query->compile(),
        );

        $this->assertSame(
            ['Chapter 02', 10],
            $query->bindings,
        );
    }

    #[Test]
    public function model_values_get_serialized(): void
    {
        $author = Author::new(
            id: new PrimaryKey(10),
        );

        $query = query($author)
            ->update(
                type: AuthorType::A,
            )
            ->build();

        $this->assertSame(
            ['a', 10],
            $query->bindings,
        );
    }

    #[Test]
    public function insert_new_relation_on_update(): void
    {
        $book = Book::new(
            id: new PrimaryKey(10),
        );

        $bookQuery = query($book)
            ->update(author: Author::new(name: 'Brent'))
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `books` SET `author_id` = ? WHERE `books`.`id` = ?',
            $bookQuery->compile(),
        );

        $this->assertInstanceOf(Query::class, $bookQuery->bindings[0]);

        $authorQuery = $bookQuery->bindings[0];

        $expected = 'INSERT INTO `authors` (`name`) VALUES (?)';

        if ($this->container->get(Database::class)->dialect === DatabaseDialect::POSTGRESQL) {
            $expected .= ' RETURNING *';
        }

        $this->assertSameWithoutBackticks($expected, $authorQuery->compile());

        $this->assertSame(['Brent'], $authorQuery->bindings);
    }

    #[Test]
    public function attach_existing_relation_on_update(): void
    {
        $book = Book::new(
            id: new PrimaryKey(10),
        );

        $bookQuery = query($book)
            ->update(author: Author::new(id: new PrimaryKey(5), name: 'Brent'))
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `books` SET `author_id` = ? WHERE `books`.`id` = ?',
            $bookQuery->compile(),
        );

        $this->assertSame([5, 10], $bookQuery->bindings);
    }

    #[Test]
    public function update_has_many_relation_without_primary_key(): void
    {
        $this->expectException(CouldNotUpdateRelation::class);

        query(Book::class)
            ->update(
                title: 'Timeline Taxi',
                chapters: [['title' => 'Chapter 01']],
            )
            ->allowAll()
            ->build();
    }

    #[Test]
    public function update_on_plain_table_with_conditions(): void
    {
        $query = query('chapters')
            ->update(
                title: 'Chapter 01',
                index: 1,
            )
            ->when(
                true,
                fn (UpdateQueryBuilder $query) => $query->whereRaw('`id` = ?', 10),
            )
            ->when(
                false,
                fn (UpdateQueryBuilder $query) => $query->whereRaw('`id` = ?', 20),
            )
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `chapters` SET `title` = ?, `index` = ? WHERE `id` = ?',
            $query->compile(),
        );

        $this->assertSame(
            ['Chapter 01', 1, 10],
            $query->bindings,
        );
    }

    #[Test]
    public function update_with_non_object_model(): void
    {
        $this->database->migrate(CreateMigrationsTable::class, CreatePublishersTable::class, CreateAuthorTable::class);

        query('authors')
            ->insert(
                ['id' => 1, 'name' => 'Brent'],
                ['id' => 2, 'name' => 'Other'],
            )
            ->execute();

        query('authors')
            ->update(
                name: 'Brendt',
            )
            ->whereRaw('id = ?', 1)
            ->execute();

        $count = query('authors')->count()->whereRaw('name = ?', 'Brendt')->execute();

        $this->assertSame(1, $count);
    }

    #[Test]
    public function multiple_where(): void
    {
        $sql = query('books')
            ->update(
                title: 'Timeline Taxi',
            )
            ->whereRaw('title = ?', 'a')
            ->whereRaw('author_id = ?', 1)
            ->whereRaw('OR author_id = ?', 2)
            ->whereRaw('AND author_id <> NULL')
            ->compile();

        $expected = 'UPDATE `books` SET title = ? WHERE title = ? AND author_id = ? OR author_id = ? AND author_id <> NULL';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function multiple_where_field(): void
    {
        $sql = query('books')
            ->update(
                title: 'Timeline Taxi',
            )
            ->where('title', 'a')
            ->where('author_id', 1)
            ->compile();

        $expected = 'UPDATE `books` SET title = ? WHERE books.title = ? AND books.author_id = ?';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function nested_where_with_update_query(): void
    {
        $query = query('books')
            ->update(status: 'archived')
            ->whereRaw('published = ?', true)
            ->andWhereGroup(function ($group): void {
                $group
                    ->whereRaw('views < ?', 100)
                    ->orWhereRaw('last_accessed < ?', '2023-01-01');
            })
            ->build();

        $expected = 'UPDATE books SET status = ? WHERE published = ? AND (views < ? OR last_accessed < ?)';

        $this->assertSameWithoutBackticks($expected, $query->compile());
        $this->assertSame(['archived', true, 100, '2023-01-01'], $query->bindings);
    }

    #[Test]
    public function update_mapping(): void
    {
        $author = Author::new(id: new PrimaryKey(1), name: 'original');

        $query = query($author)
            ->update(name: 'other')
            ->build();

        $dialect = $this->container->get(Database::class)->dialect;

        $expected = match ($dialect) {
            DatabaseDialect::POSTGRESQL => <<<'SQL'
            UPDATE "authors" SET "name" = ? WHERE "authors"."id" = ?
            SQL,
            default => <<<'SQL'
            UPDATE `authors` SET `name` = ? WHERE `authors`.`id` = ?
            SQL,
        };

        $this->assertSame($expected, $query->compile()->toString());

        $this->assertSame(['other', 1], $query->bindings);
    }

    #[Test]
    public function update_with_where_in(): void
    {
        $sql = query('books')
            ->update(title: 'Updated Book')
            ->whereIn('id', [1, 2, 3])
            ->compile();

        $expected = 'UPDATE `books` SET `title` = ? WHERE `books`.`id` IN (?,?,?)';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function update_with_where_not_in(): void
    {
        $sql = query('books')
            ->update(title: 'Updated Book')
            ->whereNotIn('author_id', [1, 2])
            ->compile();

        $expected = 'UPDATE `books` SET `title` = ? WHERE `books`.`author_id` NOT IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function update_with_where_null(): void
    {
        $sql = query('books')
            ->update(title: 'Updated Book')
            ->whereNull('author_id')
            ->compile();

        $expected = 'UPDATE `books` SET `title` = ? WHERE `books`.`author_id` IS NULL';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function update_with_where_not_null(): void
    {
        $sql = query('books')
            ->update(title: 'Updated Book')
            ->whereNotNull('author_id')
            ->compile();

        $expected = 'UPDATE `books` SET `title` = ? WHERE `books`.`author_id` IS NOT NULL';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function update_with_where_between(): void
    {
        $sql = query('books')
            ->update(title: 'Updated Book')
            ->whereBetween('id', 1, 100)
            ->compile();

        $expected = 'UPDATE `books` SET `title` = ? WHERE `books`.`id` BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function update_with_where_not_between(): void
    {
        $sql = query('books')
            ->update(title: 'Updated Book')
            ->whereNotBetween('id', 1, 10)
            ->compile();

        $expected = 'UPDATE `books` SET `title` = ? WHERE `books`.`id` NOT BETWEEN ? AND ?';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function update_with_or_where_in(): void
    {
        $sql = query('books')
            ->update(title: 'Updated Book')
            ->whereIn('id', [1, 2])
            ->orWhereIn('author_id', [10, 20])
            ->compile();

        $expected = 'UPDATE `books` SET `title` = ? WHERE `books`.`id` IN (?,?) OR `books`.`author_id` IN (?,?)';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function update_captures_primary_key_for_relations_with_convenience_where_methods(): void
    {
        $sql = query(Book::class)
            ->update(title: 'Updated Book')
            ->whereIn('id', [5])
            ->compile();

        $expected = 'UPDATE `books` SET `title` = ? WHERE `books`.`id` IN (?)';

        $this->assertSameWithoutBackticks($expected, $sql);
    }

    #[Test]
    public function throws_exception_when_relation_update_with_non_primary_key_where(): void
    {
        $this->expectException(CouldNotUpdateRelation::class);

        query(TestModelWithRelations::class)
            ->update(items: [['name' => 'Test Item']])
            ->where('name', 'some name')
            ->execute();
    }

    #[Test]
    public function throws_exception_when_relation_update_with_whereIn_multiple_values(): void
    {
        $this->expectException(CouldNotUpdateRelation::class);

        query(TestModelWithRelations::class)
            ->update(items: [['name' => 'Test Item']])
            ->whereIn('id', [1, 2])
            ->execute();
    }

    #[Test]
    public function throws_exception_when_relation_update_with_whereNotIn(): void
    {
        $this->expectException(CouldNotUpdateRelation::class);

        query(TestModelWithRelations::class)
            ->update(items: [['name' => 'Test Item']])
            ->whereNotIn('id', [999])
            ->execute();
    }

    #[Test]
    public function throws_exception_when_relation_update_with_whereBetween(): void
    {
        $this->expectException(CouldNotUpdateRelation::class);

        query(TestModelWithRelations::class)
            ->update(items: [['name' => 'Test Item']])
            ->whereBetween('id', 1, 10)
            ->execute();
    }

    #[Test]
    public function throws_exception_when_relation_update_with_whereNot(): void
    {
        $this->expectException(CouldNotUpdateRelation::class);

        query(TestModelWithRelations::class)
            ->update(items: [['name' => 'Test Item']])
            ->whereNot('id', 999)
            ->execute();
    }

    #[Test]
    public function update_skips_has_many_through_property(): void
    {
        $tag = Tag::new(
            id: new PrimaryKey(value: 1),
            label: 'php',
        );

        $query = query(model: $tag)
            ->update(label: 'php8')
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `tags` SET `label` = ? WHERE `tags`.`id` = ?',
            $query->compile(),
        );

        $this->assertSame(['php8', 1], $query->bindings);
    }

    #[Test]
    public function update_skips_has_one_through_property(): void
    {
        $tag = Tag::new(
            id: new PrimaryKey(value: 1),
            label: 'php',
        );

        $query = query(model: $tag)
            ->update(label: 'php8')
            ->build();

        $this->assertSameWithoutBackticks(
            'UPDATE `tags` SET `label` = ? WHERE `tags`.`id` = ?',
            $query->compile(),
        );

        $this->assertSame(['php8', 1], $query->bindings);
    }

    #[Test]
    public function update_with_belongs_to_many_syncs_pivot_rows(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateTagTable::class,
            CreateBookTagTable::class,
        );

        $tagId = query(model: Tag::class)->insert(
            Tag::new(
                label: 'php',
                books: [
                    Book::new(title: 'Book One'),
                ],
            ),
        )->execute();

        // Update: replace book1 with book2 and book3
        query(model: Tag::class)
            ->update(
                books: [
                    Book::new(title: 'Book Two'),
                    Book::new(title: 'Book Three'),
                ],
            )
            ->whereField(field: 'id', value: $tagId)
            ->execute();

        $bookCount = query(model: 'books')->count()->execute();
        $pivotCount = query(model: 'books_tags')->count()->execute();

        $this->assertSame(3, $bookCount);
        $this->assertSame(2, $pivotCount);
    }

    #[Test]
    public function update_query_builder_from_another_query_builder(): void
    {
        $query = query(Chapter::class)
            ->select()
            ->where('book_id', 1);

        $this->assertCount(1, UpdateQueryBuilder::fromQueryBuilder($query, ['title' => 'Updated Title'])->wheres);
    }
}

#[Table('test_models')]
final class TestModelWithRelations
{
    use IsDatabaseModel;

    #[HasMany]
    public array $items = [];

    public function __construct(
        public string $name = '',
    ) {}
}
