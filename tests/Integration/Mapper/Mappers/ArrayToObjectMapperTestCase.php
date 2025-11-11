<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Mapper\Mappers;

use DateTimeImmutable;
use InvalidArgumentException;
use Tempest\Http\Method;
use Tempest\Mapper\Exceptions\MappingValuesWereMissing;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithBuiltInCasters;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithDefaultValues;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithDoubleStringCaster;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithEnum;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMagicGetter;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithMyObject;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithStringOrObjectUnion;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithStringsArray;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithUnionArray;
use Tests\Tempest\Integration\Mapper\Fixtures\ParentObject;
use Tests\Tempest\Integration\Mapper\Fixtures\ParentWithChildrenObject;

use function Tempest\map;

/**
 * @internal
 */
final class ArrayToObjectMapperTestCase extends FrameworkIntegrationTestCase
{
    public function test_missing_values(): void
    {
        $this->expectException(MappingValuesWereMissing::class);

        map([])->to(ObjectA::class);
    }

    public function test_map_to_existing_object(): void
    {
        $object = map(['a' => 'a', 'b' => 'b'])->to(new ObjectA('', ''));

        $this->assertSame('a', $object->a);
        $this->assertSame('b', $object->b);
    }

    public function test_caster_on_property(): void
    {
        $object = map(['prop' => 'a'])->to(ObjectWithDoubleStringCaster::class);

        $this->assertSame('aa', $object->prop);
    }

    public function test_caster_on_target_object(): void
    {
        $object = map(['obj' => 'name'])->to(ObjectWithMyObject::class);

        $this->assertSame('name', $object->obj->name);
    }

    public function test_default_values(): void
    {
        $object = map([])->to(ObjectWithDefaultValues::class);

        $this->assertSame('a', $object->a);
        $this->assertSame(null, $object->b);
    }

    public function test_built_in_casters(): void
    {
        $object = map([
            'nullableDateTimeImmutable' => '',
            'dateTimeObject' => new DateTimeImmutable('2024-01-01 10:10:10'),
            'dateTimeImmutable' => '2024-01-01 10:10:10',
            'dateTime' => '2024-01-01 10:10:10',
            'dateTimeWithFormat' => '01/12/2024 10:10:10',
            'bool' => 'no',
            'float' => '0.1',
            'int' => '1',
        ])
            ->to(ObjectWithBuiltInCasters::class);

        $this->assertSame('2024-01-01 10:10:10', $object->dateTimeObject->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 10:10:10', $object->dateTimeImmutable->format('Y-m-d H:i:s'));
        $this->assertSame('2024-01-01 10:10:10', $object->dateTime->format('Y-m-d H:i:s'));
        $this->assertSame('2024-12-01 10:10:10', $object->dateTimeWithFormat->format('Y-m-d H:i:s'));
        $this->assertNull($object->nullableDateTimeImmutable);
        $this->assertSame(false, $object->bool);
        $this->assertSame(0.1, $object->float);
        $this->assertSame(1, $object->int);
    }

    public function test_wrongly_formatted_date(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must be a valid date in the format Y-m-d H:i:s');

        map([
            'dateTimeImmutable' => '01/12/2024',
        ])
            ->to(ObjectWithBuiltInCasters::class);
    }

    public function test_parent_child(): void
    {
        $parent = map(
            [
                'name' => 'a',
                'child' => ['name' => 'b'],
            ],
        )
            ->to(ParentObject::class);

        $this->assertSame('a', $parent->name);
        $this->assertSame('b', $parent->child->name);
        $this->assertSame('a', $parent->child->parent->name);
        $this->assertSame('a', $parent->child->parentCollection[0]->name);
    }

    public function test_parent_children(): void
    {
        $parent = map(
            [
                'name' => 'a',
                'children' => [['name' => 'b'], ['name' => 'c']],
            ],
        )
            ->to(ParentWithChildrenObject::class);

        $this->assertSame('a', $parent->name);

        $this->assertSame('b', $parent->children[0]->name);
        $this->assertSame('a', $parent->children[0]->parent->name);
        $this->assertSame('a', $parent->children[0]->parentCollection[0]->name);

        $this->assertSame('c', $parent->children[1]->name);
        $this->assertSame('a', $parent->children[1]->parent->name);
        $this->assertSame('a', $parent->children[1]->parentCollection[0]->name);
    }

    public function test_backed_enum_value(): void
    {
        $object = map(['method' => 'GET'])->to(ObjectWithEnum::class);

        $this->assertSame(Method::GET, $object->method);
    }

    public function test_non_strict_values_with_magic_getter(): void
    {
        $object = map([])->to(ObjectWithMagicGetter::class);

        $this->assertSame('magic', $object->a);
    }

    public function test_map_array_of_enums(): void
    {
        $object = map(['roles' => ['admin', 'user']])->to(ObjectWithArrayEnumProperty::class);

        $this->assertCount(2, $object->roles);
        $this->assertSame(EnumToBeMappedToArray::ADMIN, $object->roles[0]);
        $this->assertSame(EnumToBeMappedToArray::USER, $object->roles[1]);
    }

    public function test_map_array_of_serialized_enums(): void
    {
        $object = map(['roles' => json_encode(['admin'])])->to(ObjectWithArrayEnumProperty::class);

        $this->assertCount(1, $object->roles);
        $this->assertSame(EnumToBeMappedToArray::ADMIN, $object->roles[0]);
    }

    public function test_map_array_with_string_array_property(): void
    {
        $object = map(['items' => ['a', 'b', 'c']])->to(ObjectWithStringsArray::class);

        $this->assertCount(3, $object->items);
        $this->assertSame(
            [
                'a',
                'b',
                'c',
            ],
            $object->items,
        );
    }

    public function test_map_union_array_property_with_string(): void
    {
        $object = map(['items' => 'a'])->to(ObjectWithUnionArray::class);

        $this->assertSame(
            'a',
            $object->items,
        );
    }

    public function test_map_union_array_property_with_array(): void
    {
        $object = map(['items' => ['a', 'b', 'c']])->to(ObjectWithUnionArray::class);

        $this->assertSame(
            [
                'a',
                'b',
                'c',
            ],
            $object->items,
        );
    }

    public function test_map_union_array_property_with_null(): void
    {
        $object = map(['items' => null])->to(ObjectWithUnionArray::class);

        $this->assertNull($object->items);
    }

    public function test_map_union_string_or_object_with_string(): void
    {
        $object = map(['item' => 'string'])->to(ObjectWithStringOrObjectUnion::class);

        $this->assertIsString($object->item);
        $this->assertSame('string', $object->item);
    }

    public function test_map_union_string_or_object_with_object(): void
    {
        $object = map(['item' => ['a' => '1', 'b' => '2']])->to(ObjectWithStringOrObjectUnion::class);

        $this->assertInstanceOf(ObjectA::class, $object->item);
        $this->assertSame('1', $object->item->a);
        $this->assertSame('2', $object->item->b);
    }
}

final class ObjectWithArrayEnumProperty
{
    /** @var \Tests\Tempest\Integration\Mapper\Mappers\EnumToBeMappedToArray[] */
    public array $roles;
}

enum EnumToBeMappedToArray: string
{
    case ADMIN = 'admin';
    case USER = 'user';
}
