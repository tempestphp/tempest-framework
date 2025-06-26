<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use JsonSerializable;
use Serializable;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

final class SerializableSerializer implements Serializer
{
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
