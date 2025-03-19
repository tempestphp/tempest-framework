<?php

namespace Tempest\Mapper\Serializers;

use JsonSerializable;
use Serializable;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
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

        throw new CannotSerializeValue('JsonSerializable or Serializable');
    }
}