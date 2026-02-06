<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Database\Builder;

use Carbon\Carbon;
use DateTime as NativeDateTime;
use DateTimeImmutable;
use Tempest\Database\BelongsTo;
use Tempest\Database\Exceptions\DeleteStatementWasInvalid;
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
use Tests\Tempest\Fixtures\Migrations\CreateBookTable;
use Tests\Tempest\Fixtures\Migrations\CreateChapterTable;
use Tests\Tempest\Fixtures\Migrations\CreateIsbnTable;
use Tests\Tempest\Fixtures\Migrations\CreatePublishersTable;
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
use Tests\Tempest\Fixtures\Modules\Books\Models\Isbn;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;
use function Tempest\Mapper\map;

/**
 * @internal
 */
final class IsDatabaseModelTest extends FrameworkIntegrationTestCase
{
    public function test_create_and_update_model(): void
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

    public function test_get_with_non_id_object(): void
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

    public function test_creating_many_and_saving_preserves_model_id(): void
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

    public function test_complex_query(): void
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

    public function test_all_with_relations(): void
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

    public function test_missing_relation_exception(): void
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

    public function test_missing_value_exception(): void
    {
        $a = map([])->to(AWithValue::class);

        $this->expectException(ValueWasMissing::class);

        $name = $a->name;
    }

    public function test_nested_relations(): void
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

    public function test_load_belongs_to(): void
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

    public function test_has_many_relations(): void
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

    public function test_has_many_through_relation(): void
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

    public function test_empty_has_many_relation(): void
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

    public function test_has_one_relation(): void
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

    public function test_invalid_has_one_relation(): void
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

    public function test_lazy_load(): void
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

    public function test_eager_load(): void
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

    public function test_no_result(): void
    {
        $this->database->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        $this->assertNull(A::select()->first());
    }

    public function test_create_with_virtual_property(): void
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

    public function test_virtual_hooked_property(): void
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

    public function test_select_virtual_property(): void
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

    public function test_update_with_virtual_property(): void
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

    public function test_update_or_create(): void
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

    public function test_update_or_create_uses_initial_data_to_create(): void
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

    public function test_delete(): void
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

    public function test_delete_via_model_class_with_where_conditions(): void
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

    public function test_delete_via_model_instance_with_primary_key(): void
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

    public function test_delete_with_uninitialized_primary_key(): void
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

    public function test_delete_nonexistent_record(): void
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

    public function test_nullable_relations(): void
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

    public function test_nullable_relation_save(): void
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
