<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use Tempest\Core\Priority;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::NORMAL)]
final class IntegerSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return in_array($type->getName(), ['int', 'integer'], strict: true);
    }

    public function serialize(mixed $input): string
    {
        if (! is_int($input)) {
            throw new ValueCouldNotBeSerialized('integer');
        }

        return (string) $input;
    }
}
