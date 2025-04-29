<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;

final class BooleanSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if (! is_bool($input)) {
            throw new CannotSerializeValue('boolean');
        }

        return $input ? 'true' : 'false';
    }
}
