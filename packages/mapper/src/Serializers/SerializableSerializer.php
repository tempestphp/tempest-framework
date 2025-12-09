<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use JsonSerializable;
use Serializable;
use Tempest\Core\Priority;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

#[Priority(Priority::LOW)]
final class SerializableSerializer implements Serializer
{
    public static function for(): array
    {
        return [Serializable::class, JsonSerializable::class];
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
