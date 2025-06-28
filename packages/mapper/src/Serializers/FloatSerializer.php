<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

final class FloatSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if (! is_float($input)) {
            throw new ValueCouldNotBeSerialized('float');
        }

        return (string) $input;
    }
}
