<?php

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Mappers\ObjectToArrayMapper;
use Tempest\Mapper\Serializer;

use function Tempest\map;

final class ObjectToArraySerializer implements Serializer
{
    public function serialize(mixed $input): array
    {
        if (! is_object($input)) {
            throw new CannotSerializeValue('object');
        }

        return map($input)->with(ObjectToArrayMapper::class)->do();
    }
}
