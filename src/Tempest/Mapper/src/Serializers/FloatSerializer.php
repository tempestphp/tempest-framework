<?php

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;

final class FloatSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if (! is_float($input)) {
            throw new CannotSerializeValue('float');
        }

        return (string) $input;
    }
}
