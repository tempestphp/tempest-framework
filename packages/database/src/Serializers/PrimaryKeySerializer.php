<?php

namespace Tempest\Database\Serializers;

use Tempest\Database\PrimaryKey;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

final class PrimaryKeySerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(PrimaryKey::class);
    }

    public function serialize(mixed $input): string|int
    {
        if (! $input instanceof PrimaryKey) {
            throw new ValueCouldNotBeSerialized(PrimaryKey::class);
        }

        return $input->value;
    }
}
