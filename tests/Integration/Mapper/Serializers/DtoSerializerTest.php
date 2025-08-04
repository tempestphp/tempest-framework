<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use Tempest\Http\Method;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\Serializers\DtoSerializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\BackedEnumToSerialize;
use Tests\Tempest\Integration\Mapper\Fixtures\JsonSerializableObject;
use Tests\Tempest\Integration\Mapper\Fixtures\MyObject;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectB;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithEnum;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithNullableProperties;
use Tests\Tempest\Integration\Mapper\Fixtures\UnitEnumToSerialize;

final class DtoSerializerTest extends FrameworkIntegrationTestCase
{
    public function test_serialize(): void
    {
        $this->assertSame(
            json_encode(['type' => MyObject::class, 'data' => ['name' => 'test']]),
            new DtoSerializer(new MapperConfig())->serialize(new MyObject(name: 'test')),
        );
    }

    public function test_serialize_with_map(): void
    {
        $config = new MapperConfig()->serializeAs(MyObject::class, 'my-object');

        $this->assertSame(
            json_encode(['type' => 'my-object', 'data' => ['name' => 'test']]),
            new DtoSerializer($config)->serialize(new MyObject(name: 'test')),
        );
    }

    public function test_can_serialize_empty_array(): void
    {
        $result = new DtoSerializer(new MapperConfig())->serialize([]);

        $this->assertSame('[]', $result);
    }

    public function test_cannot_serialize_non_object_non_array(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new DtoSerializer(new MapperConfig())->serialize('string');
    }

    public function test_serialize_nested_objects(): void
    {
        $nestedA = new NestedObjectA(items: [
            new NestedObjectB(name: 'Frieren'),
            new NestedObjectB(name: 'Fern'),
        ]);

        $expected = json_encode([
            'type' => NestedObjectA::class,
            'data' => [
                'items' => [
                    [
                        'type' => NestedObjectB::class,
                        'data' => ['name' => 'Frieren'],
                    ],
                    [
                        'type' => NestedObjectB::class,
                        'data' => ['name' => 'Fern'],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer(new MapperConfig())->serialize($nestedA),
        );
    }

    public function test_serialize_object_with_nullable_properties(): void
    {
        $object = new ObjectWithNullableProperties(
            a: 'test',
            b: 3.14,
            c: null,
        );

        $expected = json_encode([
            'type' => ObjectWithNullableProperties::class,
            'data' => [
                'a' => 'test',
                'b' => 3.14,
                'c' => null,
            ],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer(new MapperConfig())->serialize($object),
        );
    }

    public function test_serialize_object_with_backed_enum(): void
    {
        $object = new ObjectWithEnum();
        $object->method = Method::POST;

        $expected = json_encode([
            'type' => ObjectWithEnum::class,
            'data' => [
                'method' => 'POST',
            ],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer(new MapperConfig())->serialize($object),
        );
    }

    public function test_serialize_object_with_unit_enum(): void
    {
        $object = new ObjectWithEnum();
        $object->method = Method::GET;

        $expected = json_encode([
            'type' => ObjectWithEnum::class,
            'data' => [
                'method' => 'GET',
            ],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer(new MapperConfig())->serialize($object),
        );
    }

    public function test_serialize_complex_nested_structure(): void
    {
        $nestedA = new NestedObjectA(items: [
            new NestedObjectB(name: 'Frieren'),
            new NestedObjectB(name: 'Fern'),
            new NestedObjectB(name: 'Stark'),
        ]);

        $expected = json_encode([
            'type' => NestedObjectA::class,
            'data' => [
                'items' => [
                    [
                        'type' => NestedObjectB::class,
                        'data' => ['name' => 'Frieren'],
                    ],
                    [
                        'type' => NestedObjectB::class,
                        'data' => ['name' => 'Fern'],
                    ],
                    [
                        'type' => NestedObjectB::class,
                        'data' => ['name' => 'Stark'],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer(new MapperConfig())->serialize($nestedA),
        );
    }

    public function test_serialize_top_level_array_of_objects(): void
    {
        $objects = [
            new MyObject(name: 'Frieren'),
            new MyObject(name: 'Fern'),
        ];

        $expected = json_encode([
            [
                'type' => MyObject::class,
                'data' => ['name' => 'Frieren'],
            ],
            [
                'type' => MyObject::class,
                'data' => ['name' => 'Fern'],
            ],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer(new MapperConfig())->serialize($objects),
        );
    }

    public function test_serialize_json_serializable_object(): void
    {
        $object = new JsonSerializableObject();

        $expected = json_encode([
            'type' => JsonSerializableObject::class,
            'data' => ['a'],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer(new MapperConfig())->serialize($object),
        );
    }

    public function test_serialize_mixed_complex_structure(): void
    {
        $nestedA = new NestedObjectA(items: [
            new NestedObjectB(name: 'Item 1'),
            new NestedObjectB(name: 'Item 2'),
        ]);

        $expected = json_encode([
            'type' => NestedObjectA::class,
            'data' => [
                'items' => [
                    [
                        'type' => NestedObjectB::class,
                        'data' => ['name' => 'Item 1'],
                    ],
                    [
                        'type' => NestedObjectB::class,
                        'data' => ['name' => 'Item 2'],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer(new MapperConfig())->serialize($nestedA),
        );
    }

    public function test_serialize_backed_enum_directly(): void
    {
        $serializer = new DtoSerializer(new MapperConfig());

        $result = $serializer->serialize([BackedEnumToSerialize::FOO]);

        $this->assertSame('["foo"]', $result);
    }

    public function test_serialize_unit_enum_directly(): void
    {
        $serializer = new DtoSerializer(new MapperConfig());

        $result = $serializer->serialize([UnitEnumToSerialize::BAR]);

        $this->assertSame('["BAR"]', $result);
    }

    public function test_serialize_with_multiple_maps(): void
    {
        $config = new MapperConfig()
            ->serializeAs(MyObject::class, 'my-object')
            ->serializeAs(NestedObjectB::class, 'nested-b');

        $object = new NestedObjectA(items: [
            new NestedObjectB(name: 'mapped'),
        ]);

        $expected = json_encode([
            'type' => NestedObjectA::class,
            'data' => [
                'items' => [
                    [
                        'type' => 'nested-b',
                        'data' => ['name' => 'mapped'],
                    ],
                ],
            ],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer($config)->serialize($object),
        );
    }

    public function test_serialize_array_with_mixed_types(): void
    {
        $objects = [
            new MyObject(name: 'test1'),
            new NestedObjectB(name: 'test2'),
        ];

        $expected = json_encode([
            [
                'type' => MyObject::class,
                'data' => ['name' => 'test1'],
            ],
            [
                'type' => NestedObjectB::class,
                'data' => ['name' => 'test2'],
            ],
        ]);

        $this->assertSame(
            $expected,
            new DtoSerializer(new MapperConfig())->serialize($objects),
        );
    }
}
