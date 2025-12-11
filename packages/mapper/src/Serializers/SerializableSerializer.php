<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use JsonSerializable;
use Serializable;
use Tempest\Core\Priority;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::LOW)]
final class SerializableSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(Serializable::class) || $type->matches(JsonSerializable::class);
    }

    public function serialize(mixed $input): array|string
    {
        if ($input instanceof JsonSerializable) {
            return $input->jsonSerialize();
        }

        if ($input instanceof Serializable) {
            return serialize($input);
        }

        throw new ValueCouldNotBeSerialized('JsonSerializable or Serializable');
    }
}
