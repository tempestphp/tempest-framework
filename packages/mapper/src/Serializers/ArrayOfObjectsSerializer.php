<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\Serializer;

use function Tempest\map;

final class ArrayOfObjectsSerializer implements Serializer
{
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
