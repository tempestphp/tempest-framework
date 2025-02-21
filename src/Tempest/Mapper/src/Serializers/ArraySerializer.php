<?php

namespace Tempest\Mapper\Serializers;

use BackedEnum;
use Tempest\Mapper\Serializer;
use UnitEnum;

class ArraySerializer implements Serializer
{
    public function serialize(mixed $input): string|null
    {
        if (is_array($input)) {
            return json_encode($input);
        }

        return null;
    }
}
