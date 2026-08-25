<?php

namespace Tests\Tempest\Integration\Database\Mappers;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Database\BelongsTo;
use Tempest\Database\BelongsToMany;
use Tempest\Database\Eager;
use Tempest\Database\Exceptions\RelationWasMissing;
use Tempest\Database\HasOne;
use Tempest\Database\IsDatabaseModel;
use Tempest\Database\Mappers\SelectModelMapper;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Database\Table;
use Tests\Tempest\Fixtures\Migrations\CreateAuthorTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateBookTagTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
use Tests\Tempest\Fixtures\Migrations\CreateTagTable;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Fixtures\Modules\Books\Models\Tag;
use Tests\Tempest\Fixtures\Modules\Books\Models\TagWithEagerBooks;
use Tests\Tempest\Fixtures\Modules\Books\Models\TagWithLazyBooks;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\inspect;
use function Tempest\Mapper\map;

final class SelectModelMapperTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function map(): void
    {
        $data = $this->data();

        $books = map($data)->with(SelectModelMapper::class)->to(Book::class);

        $this->assertCount(4, $books);
        $this->assertSame('LOTR 1', $books[0]->title);
        $this->assertSame('LOTR 2', $books[1]->title);
        $this->assertSame('LOTR 3', $books[2]->title);
        $this->assertSame('Timeline Taxi', $books[3]->title);

        $book = $books[0];
        $this->assertSame('Tolkien', $book->author->name);
        $this->assertCount(3, $book->chapters);

        $this->assertSame('LOTR 1.1', $book->chapters[0]->title);
        $this->assertSame('LOTR 1.2', $book->chapters[1]->title);
        $this->assertSame('LOTR 1.3', $book->chapters[2]->title);

        $this->assertSame('lotr-1', $book->isbn->value);
    }

    #[Test]
    public function has_many_map(): void
    {
        $data = [
            [
                'books.id' => 1,
                'books.title' => 'LOTR',
                'chapters.id' => 1,
                'chapters.title' => 'LOTR 1.1',
            ],
            [
                'books.id' => 1,
                'chapters.id' => 2,
                'chapters.title' => 'LOTR 1.2',
            ],
            [
                'books.id' => 1,
                'chapters.id' => 3,
                'chapters.title' => 'LOTR 1.3',
            ],
        ];

        $books = map($data)->with(SelectModelMapper::class)->to(Book::class);
        $this->assertCount(3, $books[0]->chapters);
        $this->assertSame('LOTR 1.1', $books[0]->chapters[0]->title);
        $this->assertSame('LOTR 1.2', $books[0]->chapters[1]->title);
        $this->assertSame('LOTR 1.3', $books[0]->chapters[2]->title);
    }

    #[Test]
    public function deeply_nested_map(): void
    {
        $data = [
            [
                'books.id' => 1,
                'books.title' => 'LOTR 1',
                'authors.name' => 'Tolkien',
                'authors.publishers.id' => 2,
                'authors.publishers.name' => 'Houghton Mifflin',
                'authors.publishers.description' => 'Hello!',
            ],
        ];

        $books = map($data)->with(SelectModelMapper::class)->to(Book::class);

        $this->assertSame('Houghton Mifflin', $books[0]->author->publisher->name);
    }

    #[Test]
    public function deeply_nested_has_many_map(): void
    {
        $data = [
            [
                'authors.id' => 1,
                'books.id' => 1,
                'books.chapters.id' => 1,
                'books.chapters.title' => 'LOTR 1.1',
            ],
            [
                'authors.id' => 1,
                'books.id' => 1,
                'books.chapters.id' => 2,
                'books.chapters.title' => 'LOTR 1.2',
            ],
        ];

        $authors = map($data)->with(SelectModelMapper::class)->to(Author::class);

        $this->assertCount(2, $authors[0]->books[0]->chapters);
        $this->assertCount(1, $authors[0]->books);
    }

    #[Test]
    public function lazy_belongs_to_many_not_eager_loaded_is_unset(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateTagTable::class,
            CreateBookTagTable::class,
        );

        Tag::create(
            label: 'PHP',
            books: [
                Book::new(title: 'Book One'),
                Book::new(title: 'Book Two'),
            ],
        );

        $tags = TagWithLazyBooks::select()->all();

        $tag = $tags[0];

        $this->assertSame(expected: 'PHP', actual: $tag->label);
        $this->assertFalse(condition: inspect(model: $tag)->isRelationLoaded(relation: 'books'));

        $this->assertCount(expectedCount: 2, haystack: $tag->books);
        $this->assertSame(expected: 'Book One', actual: $tag->books[0]->title);
        $this->assertSame(expected: 'Book Two', actual: $tag->books[1]->title);
    }

    #[Test]
    public function untagged_belongs_to_many_not_loaded_is_unset(): void
    {
        $data = [
            [
                'tags.id' => 1,
                'tags.label' => 'PHP',
            ],
        ];

        $tags = map($data)->with(mapper: SelectModelMapper::class)->to(to: Tag::class);

        $tag = $tags[0];

        $this->assertSame(expected: 'PHP', actual: $tag->label);
        $this->assertFalse(condition: inspect(model: $tag)->isRelationLoaded(relation: 'books'));

        $this->expectException(RelationWasMissing::class);
        // Accessing unset property triggers RelationWasMissing
        /** @phpstan-ignore expr.resultUnused */
        $tag->books;
    }

    #[Test]
    public function has_many_through_not_loaded_is_unset(): void
    {
        $data = [
            [
                'tags.id' => 1,
                'tags.label' => 'PHP',
            ],
        ];

        $tags = map($data)->with(mapper: SelectModelMapper::class)->to(to: Tag::class);

        $tag = $tags[0];

        $this->assertFalse(condition: inspect(model: $tag)->isRelationLoaded(relation: 'reviewers'));

        $this->expectException(RelationWasMissing::class);
        // Accessing unset property triggers RelationWasMissing
        /** @phpstan-ignore expr.resultUnused */
        $tag->reviewers;
    }

    #[Test]
    public function has_one_through_not_loaded_is_unset(): void
    {
        $data = [
            [
                'tags.id' => 1,
                'tags.label' => 'PHP',
            ],
        ];

        $tags = map($data)->with(mapper: SelectModelMapper::class)->to(to: Tag::class);

        $tag = $tags[0];

        $this->assertFalse(condition: inspect(model: $tag)->isRelationLoaded(relation: 'topReviewer'));

        $this->expectException(RelationWasMissing::class);
        // Accessing unset property triggers RelationWasMissing
        /** @phpstan-ignore expr.resultUnused */
        $tag->topReviewer;
    }

    #[Test]
    public function eager_belongs_to_many_loaded_has_books(): void
    {
        $data = [
            [
                'tags.id' => 1,
                'tags.label' => 'PHP',
                'books.id' => 1,
                'books.title' => 'LOTR 1',
            ],
            [
                'tags.id' => 1,
                'tags.label' => 'PHP',
                'books.id' => 2,
                'books.title' => 'LOTR 2',
            ],
        ];

        $tags = map($data)->with(mapper: SelectModelMapper::class)->to(to: TagWithEagerBooks::class);

        $tag = $tags[0];

        $this->assertSame(expected: 'PHP', actual: $tag->label);
        $this->assertTrue(condition: inspect(model: $tag)->isRelationLoaded(relation: 'books'));
        $this->assertCount(expectedCount: 2, haystack: $tag->books);
        $this->assertSame(expected: 'LOTR 1', actual: $tag->books[0]->title);
        $this->assertSame(expected: 'LOTR 2', actual: $tag->books[1]->title);
    }

    #[Test]
    public function belongs_to_many_loaded_with_no_results_returns_empty_array(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreatePublishersTable::class,
            CreateAuthorTable::class,
            CreateBookTable::class,
            CreateTagTable::class,
            CreateBookTagTable::class,
        );

        Tag::create(label: 'PHP');

        $tag = TagWithLazyBooks::select()->with('books')->first();

        $this->assertSame(expected: 'PHP', actual: $tag->label);
        $this->assertTrue(condition: inspect(model: $tag)->isRelationLoaded(relation: 'books'));
        $this->assertSame(expected: [], actual: $tag->books);
    }

    #[Test]
    public function has_one_not_loaded_is_unset(): void
    {
        $data = [
            [
                'books.id' => 1,
                'books.title' => 'LOTR',
            ],
        ];

        $books = map($data)->with(mapper: SelectModelMapper::class)->to(to: Book::class);

        $book = $books[0];

        $this->assertSame(expected: 'LOTR', actual: $book->title);
        $this->assertFalse(condition: inspect(model: $book)->isRelationLoaded(relation: 'isbn'));

        $this->expectException(RelationWasMissing::class);
        // Accessing unset property triggers RelationWasMissing
        /** @phpstan-ignore expr.resultUnused */
        $book->isbn;
    }

    #[Test]
    public function has_many_not_loaded_is_unset(): void
    {
        $data = [
            [
                'books.id' => 1,
                'books.title' => 'LOTR',
            ],
        ];

        $books = map($data)->with(mapper: SelectModelMapper::class)->to(to: Book::class);

        $book = $books[0];

        $this->assertSame(expected: 'LOTR', actual: $book->title);
        $this->assertFalse(condition: inspect(model: $book)->isRelationLoaded(relation: 'chapters'));

        $this->expectException(RelationWasMissing::class);
        // Accessing unset property triggers RelationWasMissing
        /** @phpstan-ignore expr.resultUnused */
        $book->chapters;
    }

    #[Test]
    public function nested_belongs_to_many_on_belongs_to(): void
    {
        $data = [
            [
                'parent_with_role.id' => 1,
                'parent_with_role.name' => 'John',
                'role.id' => 1,
                'role.name' => 'admin',
                'role.permissions.id' => 1,
                'role.permissions.label' => 'create',
            ],
            [
                'parent_with_role.id' => 1,
                'parent_with_role.name' => 'John',
                'role.id' => 1,
                'role.name' => 'admin',
                'role.permissions.id' => 2,
                'role.permissions.label' => 'delete',
            ],
        ];

        $users = map($data)->with(mapper: SelectModelMapper::class)->to(to: ParentWithRole::class);

        $user = $users[0];

        $this->assertSame(expected: 'John', actual: $user->name);
        $this->assertSame(expected: 'admin', actual: $user->role->name);
        $this->assertCount(expectedCount: 2, haystack: $user->role->permissions);
        $this->assertSame(expected: 'create', actual: $user->role->permissions[0]->label);
        $this->assertSame(expected: 'delete', actual: $user->role->permissions[1]->label);
    }

    #[Test]
    public function array_of_serialized_enums(): void
    {
        $users = map([['id' => 1, 'roles' => json_encode(['admin', 'user'])]])
            ->with(SelectModelMapper::class)
            ->to(ObjectWithArrayEnumProperty::class);

        $this->assertCount(2, $users[0]->roles);
        $this->assertSame(EnumToBeMappedToArray::ADMIN, $users[0]->roles[0]);
        $this->assertSame(EnumToBeMappedToArray::USER, $users[0]->roles[1]);
    }

    #[Test]
    public function nullable_eager_relation_with_nested_empty_relations_maps_to_null(): void
    {
        $users = map([
            [
                'users.id' => 1,
                'users.name' => 'Rado',
                'profile.id' => null,
                'profile.createdBy.id' => null,
                'profile.createdBy.name' => null,
                'profile.updatedBy.id' => null,
                'profile.updatedBy.name' => null,
            ],
        ])->with(mapper: SelectModelMapper::class)->to(to: UserWithNullableEagerProfile::class);

        $this->assertSame(expected: 'Rado', actual: $users[0]->name);
        $this->assertNull(actual: $users[0]->profile);
    }

    private function data(): array
    {
        return [
            0 => [
                'books.id' => 1,
                'authors.id' => 2,
                'chapters.id' => 1,
                'isbns.id' => 1,
                'books.title' => 'LOTR 1',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 1.1',
                'isbns.value' => 'lotr-1',
            ],
            1 => [
                'books.id' => 1,
                'authors.id' => 2,
                'chapters.id' => 2,
                'isbns.id' => 1,
                'books.title' => 'LOTR 1',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 1.2',
                'isbns.value' => 'lotr-1',
            ],
            2 => [
                'books.id' => 1,
                'authors.id' => 2,
                'chapters.id' => 3,
                'isbns.id' => 1,
                'books.title' => 'LOTR 1',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 1.3',
                'isbns.value' => 'lotr-1',
            ],
            3 => [
                'books.id' => 2,
                'authors.id' => 2,
                'chapters.id' => 4,
                'isbns.id' => 2,
                'books.title' => 'LOTR 2',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 2.1',
                'isbns.value' => 'lotr-2',
            ],
            4 => [
                'books.id' => 2,
                'authors.id' => 2,
                'chapters.id' => 5,
                'isbns.id' => 2,
                'books.title' => 'LOTR 2',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 2.2',
                'isbns.value' => 'lotr-2',
            ],
            5 => [
                'books.id' => 2,
                'authors.id' => 2,
                'chapters.id' => 6,
                'isbns.id' => 2,
                'books.title' => 'LOTR 2',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 2.3',
                'isbns.value' => 'lotr-2',
            ],
            6 => [
                'books.id' => 3,
                'authors.id' => 2,
                'chapters.id' => 7,
                'isbns.id' => 3,
                'books.title' => 'LOTR 3',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 3.1',
                'isbns.value' => 'lotr-3',
            ],
            7 => [
                'books.id' => 3,
                'authors.id' => 2,
                'chapters.id' => 8,
                'isbns.id' => 3,
                'books.title' => 'LOTR 3',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 3.2',
                'isbns.value' => 'lotr-3',
            ],
            8 => [
                'books.id' => 3,
                'authors.id' => 2,
                'chapters.id' => 9,
                'isbns.id' => 3,
                'books.title' => 'LOTR 3',
                'authors.name' => 'Tolkien',
                'chapters.title' => 'LOTR 3.3',
                'isbns.value' => 'lotr-3',
            ],
            9 => [
                'books.id' => 4,
                'authors.id' => 1,
                'chapters.id' => 10,
                'isbns.id' => 4,
                'books.title' => 'Timeline Taxi',
                'authors.name' => 'Brent',
                'chapters.title' => 'Timeline Taxi Chapter 1',
                'isbns.value' => 'tt',
            ],
            10 => [
                'books.id' => 4,
                'authors.id' => 1,
                'chapters.id' => 11,
                'isbns.id' => 4,
                'books.title' => 'Timeline Taxi',
                'authors.name' => 'Brent',
                'chapters.title' => 'Timeline Taxi Chapter 2',
                'isbns.value' => 'tt',
            ],
            11 => [
                'books.id' => 4,
                'authors.id' => 1,
                'chapters.id' => 12,
                'isbns.id' => 4,
                'books.title' => 'Timeline Taxi',
                'authors.name' => 'Brent',
                'chapters.title' => 'Timeline Taxi Chapter 3',
                'isbns.value' => 'tt',
            ],
            12 => [
                'books.id' => 4,
                'authors.id' => 1,
                'chapters.id' => 13,
                'isbns.id' => 4,
                'books.title' => 'Timeline Taxi',
                'authors.name' => 'Brent',
                'chapters.title' => 'Timeline Taxi Chapter 4',
                'isbns.value' => 'tt',
            ],
        ];
    }
}

final class ObjectWithArrayEnumProperty
{
    /** @var \Tests\Tempest\Integration\Database\Mappers\EnumToBeMappedToArray[] */
    public array $roles;
}

#[Table(name: 'users')]
final class UserWithNullableEagerProfile
{
    use IsDatabaseModel;

    public string $name;

    #[Eager]
    #[HasOne]
    public ?NullableEagerProfile $profile = null;
}

#[Table(name: 'profiles')]
final class NullableEagerProfile
{
    use IsDatabaseModel;

    #[Eager]
    #[BelongsTo]
    public ?NullableEagerUserLookup $createdBy = null;

    #[Eager]
    #[BelongsTo]
    public ?NullableEagerUserLookup $updatedBy = null;
}

#[Table(name: 'users')]
final class NullableEagerUserLookup
{
    use IsDatabaseModel;

    public string $name;
}

enum EnumToBeMappedToArray: string
{
    case ADMIN = 'admin';
    case USER = 'user';
}

#[Table(name: 'parent_with_role')]
final class ParentWithRole
{
    use IsDatabaseModel;

    public string $name;

    public ?RoleWithPermissions $role = null;
}

#[Table(name: 'roles')]
final class RoleWithPermissions
{
    use IsDatabaseModel;

    public string $name;

    /** @var \Tests\Tempest\Integration\Database\Mappers\Permission[] */
    #[BelongsToMany]
    public array $permissions = [];
}

#[Table(name: 'permissions')]
final class Permission
{
    use IsDatabaseModel;

    public string $label;
}
