<?php

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\Serializer;

use function Tempest\map;

final class ObjectToArraySerializer implements Serializer
{
    public function serialize(mixed $input): array
    {
        $values = [];

        foreach ($input as $key => $item) {
            $values[$key] = map($item)->with(ObjectToArrayMapper::class)->do();
        }

        return $values;
    }
}
