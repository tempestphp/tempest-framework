<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper;

use DateTimeImmutable;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\Mapper\Exceptions\MappingValuesWereMissing;
use Tests\Tempest\Fixtures\Modules\Books\Models\Author;
use Tests\Tempest\Fixtures\Modules\Books\Models\AuthorType;
use Tests\Tempest\Fixtures\Modules\Books\Models\Book;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\EnumToCast;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectB;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectFactoryA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectThatShouldUseCasters;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithInterfaceTypedProperties;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMapFromAttribute;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMapToAttribute;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMapToCollisions;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMapToCollisionsJsonSerializable;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMultipleMapFrom;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithStrictOnClass;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithStrictProperty;
use Tests\Tempest\Integration\Mapper\Fixtures\Person;

use function Tempest\Mapper\make;
use function Tempest\Mapper\map;

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
        $this->assertSame(1, $author->id->value);
    }

    public function test_make_collection(): void
    {
        $authors = make(Author::class)
            ->collection()
            ->from([
                [
                    'id' => 1,
                    'name' => 'test',
                ],
            ]);

        $this->assertCount(1, $authors);
        $this->assertSame('test', $authors[0]->name);
        $this->assertSame(1, $authors[0]->id->value);
    }

    public function test_make_object_from_existing_object(): void
    {
        $author = Author::new(
            name: 'original',
        );

        $author = make($author)
            ->from([
                'id' => 1,
                'name' => 'other',
            ]);

        $this->assertSame('other', $author->name);
        $this->assertSame(1, $author->id->value);
    }

    public function test_make_object_with_map_to(): void
    {
        $author = Author::new(
            name: 'original',
        );

        $author = map([
            'id' => 1,
            'name' => 'other',
        ])
            ->to($author);

        $this->assertSame('other', $author->name);
        $this->assertSame(1, $author->id->value);
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
            ),
        ])
            ->to(Book::class);

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
        } catch (MappingValuesWereMissing $mappingValuesWereMissing) {
            $this->assertStringContainsString(': a', $mappingValuesWereMissing->getMessage());
            $this->assertStringNotContainsString(': a, b', $mappingValuesWereMissing->getMessage());
        }
    }

    public function test_make_object_with_missing_values_throws_exception_for_strict_class(): void
    {
        try {
            make(ObjectWithStrictOnClass::class)->from([]);
        } catch (MappingValuesWereMissing $mappingValuesWereMissing) {
            $this->assertStringContainsString(': a, b', $mappingValuesWereMissing->getMessage());
        }
    }

    public function test_caster_on_field(): void
    {
        $object = make(ObjectFactoryA::class)
            ->from([
                'prop' => [],
            ]);

        $this->assertSame('casted', $object->prop);
    }

    public function test_map_from_attribute(): void
    {
        $object = map([
            'name' => 'Guillaume',
        ])
            ->to(ObjectWithMapFromAttribute::class);

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

        $this->assertSame(
            expected: [
                'name' => 'my first name',
                'full_name' => 'my name',
                'last_name' => 'my last name',
            ],
            actual: $array,
        );
    }

    public function test_map_to_array_with_json_serializable(): void
    {
        $array = map(new ObjectWithMapToCollisionsJsonSerializable(
            first_name: 'my first name',
            name: 'my name',
            last_name: 'my last name',
        ))->toArray();

        $this->assertSame(
            expected: [
                'first_name' => 'my first name',
                'name' => 'my name',
            ],
            actual: $array,
        );
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

    public function test_object_to_array_mapper_use_serializers(): void
    {
        $this->assertSame(
            expected: [
                'name' => 'Guillaume',
                'nativeDate' => '2025-03-02',
                'date' => '2024-01-01',
                'enum' => 'foo',
            ],
            actual: map(new ObjectThatShouldUseCasters(
                name: 'Guillaume',
                nativeDate: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2025-03-02 00:00:00'),
                date: DateTime::parse('2024-01-01 00:00:00'),
                enum: EnumToCast::FOO,
            ))->toArray(),
        );
    }

    public function test_map_two_way(): void
    {
        $object = new ObjectThatShouldUseCasters(
            name: 'Guillaume',
            nativeDate: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', '2025-03-02 00:00:00'),
            date: DateTime::parse('2024-01-01 00:00:00'),
            enum: EnumToCast::FOO,
        );

        $array = map($object)->toArray();
        $json = map($object)->toJson();

        $this->assertSame(
            expected: [
                'name' => 'Guillaume',
                'nativeDate' => '2025-03-02',
                'date' => '2024-01-01',
                'enum' => 'foo',
            ],
            actual: $array,
        );

        $this->assertSame('{"name":"Guillaume","nativeDate":"2025-03-02","date":"2024-01-01","enum":"foo"}', $json);

        $fromJson = map($json)->to(ObjectThatShouldUseCasters::class);
        $fromArray = map($array)->to(ObjectThatShouldUseCasters::class);

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestIncomplete('`fromJson` becomes an array instead of the proper object, only on Windows.');
        }

        $this->assertInstanceOf(ObjectThatShouldUseCasters::class, $fromJson);
        $this->assertSame('Guillaume', $fromJson->name);
        $this->assertSame(EnumToCast::FOO, $fromJson->enum);
        $this->assertInstanceOf(DateTimeInterface::class, $fromJson->date);
        $this->assertSame('2024-01-01', $fromJson->date->format('yyyy-MM-dd'));
        $this->assertInstanceOf(DateTimeImmutable::class, $fromJson->nativeDate);
        $this->assertSame('2025-03-02', $fromJson->nativeDate->format('Y-m-d'));

        $this->assertInstanceOf(ObjectThatShouldUseCasters::class, $fromArray);
        $this->assertSame('Guillaume', $fromArray->name);
        $this->assertSame(EnumToCast::FOO, $fromArray->enum);
        $this->assertInstanceOf(DateTimeInterface::class, $fromArray->date);
        $this->assertSame('2024-01-01', $fromArray->date->format('yyyy-MM-dd'));
        $this->assertInstanceOf(DateTimeImmutable::class, $fromArray->nativeDate);
        $this->assertSame('2025-03-02', $fromArray->nativeDate->format('Y-m-d'));
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
        ])
            ->to(ObjectWithMapFromAttribute::class);

        $this->assertSame('Guillaume', $object->fullName);
    }

    public function test_nested_object_to_array_casting(): void
    {
        $object = new NestedObjectA(
            items: [
                new NestedObjectB('a'),
                new NestedObjectB('b'),
            ],
        );

        $array = map($object)->toArray();

        $this->assertSame(
            expected: [
                'items' => [
                    ['name' => 'a'],
                    ['name' => 'b'],
                ],
            ],
            actual: $array,
        );
    }

    public function test_array_of_objects_to_array(): void
    {
        $objects = [
            new ObjectA('a', 'b'),
            new ObjectA('c', 'd'),
            new NestedObjectA(
                items: [
                    new NestedObjectB('a'),
                    new NestedObjectB('b'),
                ],
            ),
        ];

        $array = map($objects)->collection()->toArray();

        $this->assertSame(
            expected: [
                ['a' => 'a', 'b' => 'b'],
                ['a' => 'c', 'b' => 'd'],
                ['items' => [['name' => 'a'], ['name' => 'b']]],
            ],
            actual: $array,
        );
    }

    public function test_cast_with_and_serialize_with_from_interface(): void
    {
        $object = make(ObjectWithInterfaceTypedProperties::class)->from([
            'castable' => 'test-value',
            'serializable' => 'another-value',
        ]);

        $this->assertSame('casted:test-value', $object->castable->getValue());
        $this->assertSame('casted:another-value', $object->serializable->getValue());

        $array = map($object)->toArray();

        $this->assertSame('serialized:casted:test-value', $array['castable']);
        $this->assertSame('serialized:casted:another-value', $array['serializable']);
    }
}
