<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\ConfigurableSerializer;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\MappingContext;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Support\Priority;

use function Tempest\Mapper\map;

#[Priority(Priority::HIGHEST)]
final class ArrayOfObjectsSerializer implements Serializer, DynamicSerializer, ConfigurableSerializer
{
    private readonly Context $context;

    public function __construct(
        ?Context $context = null,
    ) {
        $this->context = $context ?? MappingContext::default();
    }

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        if ($input instanceof TypeReflector) {
            return false;
        }

        return $input->getIterableType() instanceof TypeReflector;
    }

    public static function configure(PropertyReflector|TypeReflector|string $input, Context $context): Serializer
    {
        return new self($context);
    }

    public function serialize(mixed $input): array
    {
        if (!is_array($input)) {
            throw new ValueCouldNotBeSerialized('array');
        }

        $values = [];

        foreach ($input as $key => $object) {
            $values[$key] = map($object)
                ->in($this->context)
                ->with(ObjectToArrayMapper::class)
                ->do();
        }

        return $values;
    }
}
