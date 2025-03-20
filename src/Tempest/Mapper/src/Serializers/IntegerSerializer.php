<?php

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;

final class IntegerSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if (! is_int($input)) {
            throw new CannotSerializeValue('integer');
        }

        return (string) $input;
    }
}
