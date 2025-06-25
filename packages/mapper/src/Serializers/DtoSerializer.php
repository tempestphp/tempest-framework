<?php

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\CannotCastValue;
use Tempest\Mapper\Serializer;

use function Tempest\map;

final class DtoSerializer implements Serializer
{
    public function serialize(mixed $input): array|string
    {
        if (! is_object($input)) {
            throw new CannotCastValue('object');
        }

        $data = map($input)->toArray();

        return json_encode([
            'type' => get_class($input),
            'data' => $data,
        ]);
    }
}
