<?php

namespace Tempest\Mapper\Serializers;

use Stringable;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;

final class StringSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if (! is_string($input) && ! ($input instanceof Stringable)) {
            throw new CannotSerializeValue('string');
        }

        return (string) $input;
    }
}
