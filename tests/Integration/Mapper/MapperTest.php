<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use DateTimeImmutable;
use Tempest\Database\Id;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\Exceptions\MissingValuesException;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Reflection\ClassReflector;
use Tempest\Validation\Exceptions\ValidationException;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\EnumToCast;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectB;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectFactoryA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectFactoryWithValidation;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectThatShouldUseCasters;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithBoolProp;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithFloatProp;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithIntProp;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMapFromAttribute;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMapToAttribute;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMapToCollisions;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMapToCollisionsJsonSerializable;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMultipleMapFrom;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithNotNullInferenceA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithStrictOnClass;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithStrictProperty;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithStringProperty;
use Tests\Tempest\Integration\Mapper\Fixtures\Person;
use function Tempest\make;
use function Tempest\map;

/**
 * @internal
 */
final class MapperTest extends FrameworkIntegrationTestCase
{
    public function test_make_object_from_class_string(): void
    {
        $author = make(Author::class)->from([
            'id' => 1,
            'name' => 'test',
        ]);

        $this->assertSame('test', $author->name);
        $this->assertSame(1, $author->id->id);
    }

    public function test_make_collection(): void
    {
        $authors = make(Author::class)->collection()->from([
            [
                'id' => 1,
                'name' => 'test',
            ],
        ]);

        $this->assertCount(1, $authors);
        $this->assertSame('test', $authors[0]->name);
        $this->assertSame(1, $authors[0]->id->id);
    }

    public function test_make_object_from_existing_object(): void
    {
        $author = Author::new(
            name: 'original',
        );

        $author = make($author)->from([
            'id' => 1,
            'name' => 'other',
        ]);

        $this->assertSame('other', $author->name);
        $this->assertSame(1, $author->id->id);
    }

    public function test_make_object_with_map_to(): void
    {
        $author = Author::new(
            name: 'original',
        );

        $author = map([
            'id' => 1,
            'name' => 'other',
        ])->to($author);

        $this->assertSame('other', $author->name);
        $this->assertSame(1, $author->id->id);
    }

    public function test_make_object_with_has_many_relation(): void
    {
        $author = make(Author::class)->from([
            'name' => 'test',
            'books' => [
                ['title' => 'a'],
                ['title' => 'b'],
            ],
        ]);

        $this->assertSame('test', $author->name);
        $this->assertCount(2, $author->books);
        $this->assertSame('a', $author->books[0]->title);
        $this->assertSame('b', $author->books[1]->title);
        $this->assertSame('test', $author->books[0]->author->name);
    }

    public function test_make_object_with_one_to_one_relation(): void
    {
        $book = make(Book::class)->from([
            'title' => 'test',
            'author' => [
                'name' => 'author',
            ],
        ]);

        $this->assertSame('test', $book->title);
        $this->assertSame('author', $book->author->name);
        $this->assertSame('test', $book->author->books[0]->title);
    }

    public function test_make_object_from_array_with_object_relation(): void
    {
        $book = map([
            'title' => 'Book Title',
            'author' => new Author(
                name: 'Author name',
                type: AuthorType::B,
            )
        ])->to(Book::class);

        $this->assertSame('Book Title', $book->title);
        $this->assertSame('Author name', $book->author->name);
        $this->assertSame(AuthorType::B, $book->author->type);
    }

    public function test_can_make_non_strict_object_with_uninitialized_values(): void
    {
        $author = make(Author::class)->from([]);

        $this->assertFalse(isset($author->name));
    }

    public function test_make_object_with_missing_values_throws_exception_for_strict_property(): void
    {
        try {
            make(ObjectWithStrictProperty::class)->from([]);
        } catch (MissingValuesException $missingValuesException) {
            $this->assertStringContainsString(': a', $missingValuesException->getMessage());
            $this->assertStringNotContainsString(': a, b', $missingValuesException->getMessage());
        }
    }

    public function test_make_object_with_missing_values_throws_exception_for_strict_class(): void
    {
        try {
            make(ObjectWithStrictOnClass::class)->from([]);
        } catch (MissingValuesException $missingValuesException) {
            $this->assertStringContainsString(': a, b', $missingValuesException->getMessage());
        }
    }

    public function test_caster_on_field(): void
    {
        $object = make(ObjectFactoryA::class)->from([
            'prop' => [],
        ]);

        $this->assertSame('casted', $object->prop);
    }

    public function test_validation(): void
    {
        $this->expectException(ValidationException::class);

        map(['prop' => 'a'])->to(ObjectFactoryWithValidation::class);
    }

    public function test_validation_happens_before_mapping(): void
    {
        $this->expectException(ValidationException::class);

        map(['prop' => null])->to(ObjectFactoryWithValidation::class);
    }

    public function test_validation_infers_not_null_from_property_type(): void
    {
        $this->expectException(ValidationException::class);

        map(['prop' => null])->to(ObjectWithStringProperty::class);
    }

    public function test_validation_infers_int_rule_from_property_type(): void
    {
        try {
            map(['prop' => 'a'])->to(ObjectWithIntProp::class);
        } catch (ValidationException $validationException) {
            $this->assertStringContainsString('Value should be an integer', $validationException->getMessage());
        }
    }

    public function test_validation_infers_float_rule_from_property_type(): void
    {
        try {
            map(['prop' => 'a'])->to(ObjectWithFloatProp::class);
        } catch (ValidationException $validationException) {
            $this->assertStringContainsString('Value should be a float', $validationException->getMessage());
        }
    }

    public function test_validation_infers_string_rule_from_property_type(): void
    {
        try {
            map(['prop' => 1])->to(ObjectWithStringProperty::class);
        } catch (ValidationException $validationException) {
            $this->assertStringContainsString('Value should be a string', $validationException->getMessage());
        }
    }

    public function test_validation_infers_bool_rule_from_property_type(): void
    {
        try {
            map(['prop' => 'invalid'])->to(ObjectWithBoolProp::class);
        } catch (ValidationException $validationException) {
            $this->assertStringContainsString('Value should represent a boolean value', $validationException->getMessage());
        }
    }

    public function test_map_from_attribute(): void
    {
        $object = map([
            'name' => 'Guillaume',
        ])->to(ObjectWithMapFromAttribute::class);

        $this->assertSame('Guillaume', $object->fullName);
    }

    public function test_map_to_attribute(): void
    {
        $array = map(new ObjectWithMapToAttribute(
            fullName: 'Guillaume',
        ))->toArray();

        $this->assertSame(['name' => 'Guillaume'], $array);
    }

    public function test_map_to_handle_name_collisions(): void
    {
        $array = map(new ObjectWithMapToCollisions(
            first_name: 'my first name',
            name: 'my name',
            last_name: 'my last name',
        ))->toArray();

        $this->assertSame([
            'name' => 'my first name',
            'full_name' => 'my name',
            'last_name' => 'my last name',
        ], $array);
    }

    public function test_map_to_array_with_json_serializable(): void
    {
        $array = map(new ObjectWithMapToCollisionsJsonSerializable(
            first_name: 'my first name',
            name: 'my name',
            last_name: 'my last name',
        ))->toArray();

        $this->assertSame([
            'first_name' => 'my first name',
            'name' => 'my name',
        ], $array);
    }

    public function test_nested_value_object_mapping(): void
    {
        $data = [
            'name' => [
                'first' => 'Brent',
                'last' => 'Roose',
            ],
        ];

        $person = map($data)->to(Person::class);

        $this->assertSame('Brent', $person->name->first);
        $this->assertSame('Roose', $person->name->last);
    }

    public function test_object_to_array_mapper_use_casters(): void
    {
        $this->assertSame(
            expected: [
                'name' => 'Guillaume',
                'date' => '2025-03-02',
                'enum' => 'foo',
            ],
            actual: map(new ObjectThatShouldUseCasters(
                name: 'Guillaume',
                date: DateTimeImmutable::createFromFormat('Y-m-d', '2025-03-02'),
                enum: EnumToCast::FOO,
            ))->toArray(),
        );
    }

    public function test_multiple_map_from_source(): void
    {
        $object = map(['name' => 'Guillaume'])->to(ObjectWithMultipleMapFrom::class);
        $this->assertSame('Guillaume', $object->fullName);

        $object = map(['first_name' => 'Guillaume'])->to(ObjectWithMultipleMapFrom::class);
        $this->assertSame('Guillaume', $object->fullName);
    }

    public function test_multiple_map_from_take_first_occurence(): void
    {
        $data = [
            'name' => 'Guillaume',
            'first_name' => 'John',
        ];

        $object = map($data)->to(ObjectWithMultipleMapFrom::class);
        $this->assertSame('Guillaume', $object->fullName);
    }

    public function test_multiple_map_from_fallback_to_property_name(): void
    {
        $object = map([
            'fullName' => 'Guillaume',
        ])->to(ObjectWithMapFromAttribute::class);

        $this->assertSame('Guillaume', $object->fullName);
    }

    public function test_nested_object_to_array_casting(): void
    {
        $object = new NestedObjectA(
            items: [
                new NestedObjectB('a'),
                new NestedObjectB('b'),
            ]
        );

        $array = map($object)->with(ObjectToArrayMapper::class)->do();

        $this->assertSame([
            'items' => [
                ['name' => 'a'],
                ['name' => 'b'],
            ]
        ], $array);
    }
}
