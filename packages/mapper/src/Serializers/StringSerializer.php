<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Stringable;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

final class StringSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if (! is_string($input) && ! ($input instanceof Stringable)) {
            throw new ValueCouldNotBeSerialized('string');
        }

        return (string) $input;
    }
}
