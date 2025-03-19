<?php

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;

final class ScalarSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if (! is_scalar($input)) {
            throw new CannotSerializeValue('scalar');
        }

        return serialize($input);
    }
}
