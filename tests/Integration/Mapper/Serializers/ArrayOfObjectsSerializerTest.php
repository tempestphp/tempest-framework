<?php

namespace Tests\Tempest\Integration\Mapper\Serializers;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\MappingContext;
use Tempest\Mapper\Serializer;
use Tempest\Mapper\SerializerFactory;
use Tempest\Mapper\Serializers\ArrayOfObjectsSerializer;
use Tempest\Mapper\Serializers\EnumSerializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Priority;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Integration\Mapper\Fixtures\ObjectWithSerializerProperties;

final class ArrayOfObjectsSerializerTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function serialize(): void
    {
        $this->assertSame(
            [
                [
                    'stringProp' => 'a',
                    'stringableProp' => 'a',
                    'intProp' => 1,
                    'nullableIntProp' => null,
                    'floatProp' => 0.1,
                    'nullableFloatProp' => null,
                    'boolProp' => true,
                    'nullableBoolProp' => null,
                    'arrayProp' => '["a"]',
                    'serializeWithProp' => 'aa',
                    'doubleStringProp' => 'aa',
                    'jsonSerializableObject' => [
                        0 => 'a',
                    ],
                    'serializableObject' => "O:60:\"Tests\Tempest\Integration\Mapper\Fixtures\SerializableObject\":1:{i:0;s:1:\"a\";}",
                    'nativeDateTimeImmutableProp' => '2025-01-01 00:00:00',
                    'nativeDateTimeProp' => '2025-01-01 00:00:00',
                    'nativeDateTimeInterfaceProp' => '2025-01-01 00:00:00',
                    'dateTimeProp' => '2025-01-01 00:00:00',
                    'unitEnum' => 'BAR',
                    'backedEnum' => 'foo',
                ],
            ],
            new ArrayOfObjectsSerializer()->serialize([new ObjectWithSerializerProperties()]),
        );
    }

    #[Test]
    public function only_arrays_allowed(): void
    {
        $this->expectException(ValueCouldNotBeSerialized::class);

        new EnumSerializer()->serialize('foo');
    }

    #[Test]
    public function uses_mapping_context_for_nested_objects(): void
    {
        $context = MappingContext::from('api');

        $this->container
            ->get(SerializerFactory::class)
            ->addSerializer(
                ContextStringSerializer::class,
                priority: Priority::HIGHEST,
                context: $context,
            );

        $serializer = $this->container->get(
            ArrayOfObjectsSerializer::class,
            context: $context,
        );

        $this->assertSame(
            [['name' => 'api:a']],
            $serializer->serialize([new ContextObject('a')]),
        );
    }
}

final readonly class ContextObject
{
    public function __construct(
        public string $name,
    ) {}
}

final class ContextStringSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->getName() === 'string';
    }

    public function serialize(mixed $input): string
    {
        return 'api:' . $input;
    }
}
