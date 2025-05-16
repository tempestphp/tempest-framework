<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\ORM;

use Carbon\Carbon;
use DateTimeImmutable;
use Tempest\Database\Builder\ModelDefinition;
use Tempest\Database\Exceptions\MissingRelation;
use Tempest\Database\Exceptions\MissingValue;
use Tempest\Database\Id;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\Mapper\CasterFactory;
use Tempest\Mapper\SerializerFactory;
use Tempest\Validation\Exceptions\ValidationException;
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
use Tests\Tempest\Integration\ORM\Migrations\CreateATable;
use Tests\Tempest\Integration\ORM\Migrations\CreateBTable;
use Tests\Tempest\Integration\ORM\Migrations\CreateCarbonModelTable;
use Tests\Tempest\Integration\ORM\Migrations\CreateCasterModelTable;
use Tests\Tempest\Integration\ORM\Migrations\CreateCTable;
use Tests\Tempest\Integration\ORM\Migrations\CreateHasManyChildTable;
use Tests\Tempest\Integration\ORM\Migrations\CreateHasManyParentTable;
use Tests\Tempest\Integration\ORM\Migrations\CreateHasManyThroughTable;
use Tests\Tempest\Integration\ORM\Models\AttributeTableNameModel;
use Tests\Tempest\Integration\ORM\Models\BaseModel;
use Tests\Tempest\Integration\ORM\Models\CarbonCaster;
use Tests\Tempest\Integration\ORM\Models\CarbonModel;
use Tests\Tempest\Integration\ORM\Models\CarbonSerializer;
use Tests\Tempest\Integration\ORM\Models\CasterEnum;
use Tests\Tempest\Integration\ORM\Models\CasterModel;
use Tests\Tempest\Integration\ORM\Models\ChildModel;
use Tests\Tempest\Integration\ORM\Models\ModelWithValidation;
use Tests\Tempest\Integration\ORM\Models\ParentModel;
use Tests\Tempest\Integration\ORM\Models\StaticMethodTableNameModel;
use Tests\Tempest\Integration\ORM\Models\ThroughModel;

use function Tempest\Database\model;
use function Tempest\map;

/**
 * @internal
 */
final class IsDatabaseModelTest extends FrameworkIntegrationTestCase
{
    public function test_create_and_update_model(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $foo = Foo::create(
            bar: 'baz',
        );

        $this->assertSame('baz', $foo->bar);
        $this->assertInstanceOf(Id::class, $foo->id);

        $foo = Foo::get($foo->id);

        $this->assertSame('baz', $foo->bar);
        $this->assertInstanceOf(Id::class, $foo->id);

        $foo->update(
            bar: 'boo',
        );

        $foo = Foo::get($foo->id);

        $this->assertSame('boo', $foo->bar);
    }

    public function test_creating_many_and_saving_preserves_model_id(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            FooDatabaseMigration::class,
        );

        $a = Foo::create(
            bar: 'a',
        );
        $b = Foo::create(
            bar: 'b',
        );

        $this->assertEquals(1, $a->id->id);
        $a->save();
        $this->assertEquals(1, $a->id->id);
    }

    public function test_complex_query(): void
    {
        $this->migrate(
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

        $this->assertEquals(1, $book->id->id);
        $this->assertSame('Book Title', $book->title);
        $this->assertSame(AuthorType::B, $book->author->type);
        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertSame('Author Name', $book->author->name);
        $this->assertEquals(1, $book->author->id->id);
    }

    public function test_all_with_relations(): void
    {
        $this->migrate(
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

        $this->assertEquals(1, $book->id->id);
        $this->assertSame('Book Title', $book->title);
        $this->assertSame(AuthorType::B, $book->author->type);
        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertSame('Author Name', $book->author->name);
        $this->assertEquals(1, $book->author->id->id);
    }

    public function test_missing_relation_exception(): void
    {
        $this->migrate(
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

        $this->expectException(MissingRelation::class);

        $b = $a->b;
    }

    public function test_missing_value_exception(): void
    {
        $a = map([])->to(AWithValue::class);

        $this->expectException(MissingValue::class);

        $name = $a->name;
    }

    public function test_nested_relations(): void
    {
        $this->migrate(
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
        $this->migrate(
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
        $this->migrate(
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
        $this->migrate(
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
        $this->migrate(
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
        $this->migrate(
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
        $this->migrate(
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
        $this->migrate(
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
        $this->migrate(
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
        $this->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        $this->assertNull(A::select()->first());
    }

    public function test_virtual_property(): void
    {
        $this->migrate(
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

        $this->assertSame(-$a->id->id, $a->fake);
    }

    public function test_update_or_create(): void
    {
        $this->migrate(
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

        $this->assertNull(Book::select()->whereField('title', 'A')->first());
        $this->assertNotNull(Book::select()->whereField('title', 'B')->first());
    }

    public function test_delete(): void
    {
        $this->migrate(
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

    public function test_property_with_carbon_type(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateCarbonModelTable::class,
        );

        $this->container->get(CasterFactory::class)->addCaster(Carbon::class, CarbonCaster::class);
        $this->container->get(SerializerFactory::class)->addSerializer(Carbon::class, CarbonSerializer::class);

        new CarbonModel(createdAt: new Carbon('2024-01-01'))->save();

        $model = CarbonModel::select()->first();

        $this->assertTrue($model->createdAt->equalTo(new Carbon('2024-01-01')));
    }

    public function test_two_way_casters_on_models(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateCasterModelTable::class,
        );

        new CasterModel(
            date: new DateTimeImmutable('2025-01-01 00:00:00'),
            array: ['a', 'b', 'c'],
            enum: CasterEnum::BAR,
        )->save();

        $model = CasterModel::select()->first();

        $this->assertSame(new DateTimeImmutable('2025-01-01 00:00:00')->format('c'), $model->date->format('c'));
        $this->assertSame(['a', 'b', 'c'], $model->array);
        $this->assertSame(CasterEnum::BAR, $model->enum);
    }

    public function test_find(): void
    {
        $this->migrate(
            CreateMigrationsTable::class,
            CreateATable::class,
            CreateBTable::class,
            CreateCTable::class,
        );

        new C(name: 'one')->save();
        new C(name: 'two')->save();

        /** @var C[] */
        $valid = C::find(name: 'one')->all();

        $this->assertCount(1, $valid);
        $this->assertSame($valid[0]->name, 'one');

        $invalid = C::find(name: 'three')->all();

        $this->assertCount(0, $invalid);
    }

    public function test_table_name_overrides(): void
    {
        $this->assertEquals('base_models', new ModelDefinition(BaseModel::class)->getTableDefinition()->name);
        $this->assertEquals('custom_attribute_table_name', new ModelDefinition(AttributeTableNameModel::class)->getTableDefinition()->name);
        $this->assertEquals('custom_static_method_table_name', new ModelDefinition(StaticMethodTableNameModel::class)->getTableDefinition()->name);
    }

    public function test_validation_on_create(): void
    {
        $this->expectException(ValidationException::class);

        ModelWithValidation::create(
            index: -1,
        );
    }

    public function test_validation_on_update(): void
    {
        $model = ModelWithValidation::new(
            id: new Id(1),
            index: 1,
        );

        $this->expectException(ValidationException::class);

        $model->update(
            index: -1,
        );
    }

    public function test_validation_on_new(): void
    {
        $model = ModelWithValidation::new(
            index: 1,
        );

        $model->index = -1;

        $this->expectException(ValidationException::class);

        $model->save();
    }

    public function test_skipped_validation(): void
    {
        try {
            model(ModelWithValidation::class)->validate(
                index: -1,
                skip: -1,
            );
        } catch (ValidationException $validationException) {
            $this->assertStringContainsString('index', $validationException->getMessage());
            $this->assertStringContainsString(ModelWithValidation::class, $validationException->getMessage());
            $this->assertStringNotContainsString('skip', $validationException->getMessage());
        }
    }
}
