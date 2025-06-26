<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

final class IntegerSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if (! is_int($input)) {
            throw new ValueCouldNotBeSerialized('integer');
        }

        return (string) $input;
    }
}
