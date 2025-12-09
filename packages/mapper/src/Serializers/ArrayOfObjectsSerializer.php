<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Closure;
use Tempest\Core\Priority;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;

use function Tempest\Mapper\map;

#[Priority(Priority::HIGHEST)]
final class ArrayOfObjectsSerializer implements Serializer
{
    public static function for(): Closure
    {
        return fn (PropertyReflector $property) => $property->getIterableType() !== null;
    }

    public function serialize(mixed $input): array
    {
        if (! is_array($input)) {
            throw new ValueCouldNotBeSerialized('array');
        }

        $values = [];

        foreach ($input as $key => $object) {
            $values[$key] = map($object)->with(ObjectToArrayMapper::class)->do();
        }

        return $values;
    }
}
