<?php

namespace Tests\Tempest\Integration\Mapper\Casters;

use Tempest\Http\Method;
use Tempest\Mapper\Casters\DataTransferObjectCaster;
use Tempest\Mapper\Exceptions\ValueCouldNotBeCast;
use Tempest\Mapper\MapperConfig;
use Tempest\Mapper\Serializers\DataTransferObjectSerializer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\MyObject;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectA;
use Tests\Tempest\Integration\Mapper\Fixtures\NestedObjectB;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithEnum;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithNullableProperties;

final class DataTransferObjectCasterTest extends FrameworkIntegrationTestCase
{
    public function test_cast(): void
    {
        $json = json_encode(['type' => MyObject::class, 'data' => ['name' => 'test']]);

        $dto = new DataTransferObjectCaster(new MapperConfig())->cast($json);

        $this->assertInstanceOf(MyObject::class, $dto);
        $this->assertSame('test', $dto->name);
    }

    public function test_cast_with_map(): void
    {
        $config = new MapperConfig()->serializeAs(MyObject::class, 'my-object');

        $json = json_encode(['type' => 'my-object', 'data' => ['name' => 'test']]);

        $dto = new DataTransferObjectCaster($config)->cast($json);

        $this->assertInstanceOf(MyObject::class, $dto);
        $this->assertSame('test', $dto->name);
    }

    public function test_cannot_cast_with_invalid_json(): void
    {
        $json = '';

        $this->expectException(ValueCouldNotBeCast::class);

        new DataTransferObjectCaster(new MapperConfig())->cast($json);
    }

    public function test_cast_nested_objects(): void
    {
        $json = json_encode([
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

        $dto = new DataTransferObjectCaster(new MapperConfig())->cast($json);

        $this->assertInstanceOf(NestedObjectA::class, $dto);
        $this->assertCount(2, $dto->items);
        $this->assertInstanceOf(NestedObjectB::class, $dto->items[0]);
        $this->assertSame('Frieren', $dto->items[0]->name);
        $this->assertInstanceOf(NestedObjectB::class, $dto->items[1]);
        $this->assertSame('Fern', $dto->items[1]->name);
    }

    public function test_cast_object_with_nullable_properties(): void
    {
        $json = json_encode([
            'type' => ObjectWithNullableProperties::class,
            'data' => [
                'a' => 'test',
                'b' => 3.14,
                'c' => null,
            ],
        ]);

        $dto = new DataTransferObjectCaster(new MapperConfig())->cast($json);

        $this->assertInstanceOf(ObjectWithNullableProperties::class, $dto);
        $this->assertSame('test', $dto->a);
        $this->assertSame(3.14, $dto->b);
        $this->assertNull($dto->c);
    }

    public function test_cast_object_with_enums(): void
    {
        $json = json_encode([
            'type' => ObjectWithEnum::class,
            'data' => [
                'method' => 'GET',
            ],
        ]);

        $dto = new DataTransferObjectCaster(new MapperConfig())->cast($json);

        $this->assertInstanceOf(ObjectWithEnum::class, $dto);
        $this->assertSame(Method::GET, $dto->method);
    }

    public function test_cast_array_directly(): void
    {
        $array = [
            'type' => MyObject::class,
            'data' => ['name' => 'test'],
        ];

        $dto = new DataTransferObjectCaster(new MapperConfig())->cast($array);

        $this->assertInstanceOf(MyObject::class, $dto);
        $this->assertSame('test', $dto->name);
    }

    public function test_cast_top_level_array(): void
    {
        $json = json_encode([
            [
                'type' => MyObject::class,
                'data' => ['name' => 'Frieren'],
            ],
            [
                'type' => MyObject::class,
                'data' => ['name' => 'Fern'],
            ],
        ]);

        $dto = new DataTransferObjectCaster(new MapperConfig())->cast($json);

        $this->assertIsArray($dto);
        $this->assertCount(2, $dto);
        $this->assertInstanceOf(MyObject::class, $dto[0]);
        $this->assertSame('Frieren', $dto[0]->name);
        $this->assertInstanceOf(MyObject::class, $dto[1]);
        $this->assertSame('Fern', $dto[1]->name);
    }

    public function test_cast_with_multiple_mapped_classes(): void
    {
        $config = new MapperConfig()
            ->serializeAs(MyObject::class, 'my-object')
            ->serializeAs(NestedObjectB::class, 'nested-b');

        $json = json_encode([
            'type' => 'nested-b',
            'data' => ['name' => 'mapped nested'],
        ]);

        $dto = new DataTransferObjectCaster($config)->cast($json);

        $this->assertInstanceOf(NestedObjectB::class, $dto);
        $this->assertSame('mapped nested', $dto->name);
    }

    public function test_cast_preserves_non_dto_values(): void
    {
        $originalValue = 42;

        $result = new DataTransferObjectCaster(new MapperConfig())->cast($originalValue);

        $this->assertSame($originalValue, $result);
    }

    public function test_cast_malformed_json_throws_exception(): void
    {
        $malformedJson = '{"invalid": json}';

        $this->expectException(ValueCouldNotBeCast::class);

        new DataTransferObjectCaster(new MapperConfig())->cast($malformedJson);
    }

    public function test_serialize_and_cast_roundtrip(): void
    {
        $original = new NestedObjectA(items: [
            new NestedObjectB(name: 'Frieren'),
            new NestedObjectB(name: 'Fern'),
        ]);

        $serializer = new DataTransferObjectSerializer(new MapperConfig());
        $json = $serializer->serialize($original);

        $casted = new DataTransferObjectCaster(new MapperConfig())->cast($json);

        $this->assertInstanceOf(NestedObjectA::class, $casted);
        $this->assertCount(2, $casted->items);
        $this->assertInstanceOf(NestedObjectB::class, $casted->items[0]);
        $this->assertSame('Frieren', $casted->items[0]->name);
        $this->assertInstanceOf(NestedObjectB::class, $casted->items[1]);
        $this->assertSame('Fern', $casted->items[1]->name);
    }
}
