<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Core\Priority;
use Tempest\Mapper\Context;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

#[Context(Context::DEFAULT)]
#[Priority(Priority::NORMAL)]
final class IntegerSerializer implements Serializer
{
    public static function for(): array
    {
        return ['int', 'integer'];
    }

    public function serialize(mixed $input): string
    {
        if (! is_int($input)) {
            throw new ValueCouldNotBeSerialized('integer');
        }

        return (string) $input;
    }
}
