<?php

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Support\Json;

use function Tempest\map;

final class DtoSerializer implements Serializer
{
    public function serialize(mixed $input): array|string
    {
        if (! is_object($input)) {
            throw new ValueCouldNotBeSerialized('object');
        }

        $data = map($input)->toArray();

        return Json\encode([
            'type' => get_class($input),
            'data' => $data,
        ]);
    }
}
