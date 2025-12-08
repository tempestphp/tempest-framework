<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Stringable;
use Tempest\Core\Priority;
use Tempest\Mapper\Context;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

#[Context(Context::DEFAULT)]
#[Priority(Priority::NORMAL)]
final class StringSerializer implements Serializer
{
    public static function for(): array
    {
        return ['string', Stringable::class];
    }

    public function serialize(mixed $input): string
    {
        if (! is_string($input) && ! $input instanceof Stringable) {
            throw new ValueCouldNotBeSerialized('string');
        }

        return (string) $input;
    }
}
