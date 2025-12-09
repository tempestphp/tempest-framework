<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Core\Priority;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

#[Priority(Priority::NORMAL)]
final class BooleanSerializer implements Serializer
{
    public static function for(): array
    {
        return ['bool', 'boolean'];
    }

    public function serialize(mixed $input): string
    {
        if (! is_bool($input)) {
            throw new ValueCouldNotBeSerialized('boolean');
        }

        return $input ? 'true' : 'false';
    }
}
