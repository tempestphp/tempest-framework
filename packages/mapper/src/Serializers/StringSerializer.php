<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Stringable;
use Tempest\Core\Priority;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::NORMAL)]
final class StringSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->getName() === 'string' || $type->matches(Stringable::class);
    }

    public function serialize(mixed $input): string
    {
        if (! is_string($input) && ! $input instanceof Stringable) {
            throw new ValueCouldNotBeSerialized('string');
        }

        return (string) $input;
    }
}
