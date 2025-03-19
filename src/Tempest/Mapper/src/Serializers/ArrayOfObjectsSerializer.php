<?php

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\Serializer;

use function Tempest\map;

final class ArrayOfObjectsSerializer implements Serializer
{
    public function serialize(mixed $input): array
    {
        if (! is_array($input)) {
            throw new CannotSerializeValue('array');
        }

        $values = [];

        foreach ($input as $key => $object) {
            $values[$key] = map($object)->with(ObjectToArrayMapper::class)->do();
        }

        return $values;
    }
}
