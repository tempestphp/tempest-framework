<?php

namespace Tempest\Database\Serializers;

use Tempest\Database\PrimaryKey;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

final class PrimaryKeySerializer implements Serializer
{
    public function serialize(mixed $input): array|string
    {
        if (! $input instanceof PrimaryKey) {
            throw new ValueCouldNotBeSerialized(PrimaryKey::class);
        }

        return $input->value;
    }
}
