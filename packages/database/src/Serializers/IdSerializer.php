<?php

namespace Tempest\Database\Serializers;

use Tempest\Database\Id;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

final class IdSerializer implements Serializer
{
    public function serialize(mixed $input): array|string
    {
        if (! ($input instanceof Id)) {
            throw new ValueCouldNotBeSerialized(Id::class);
        }

        return $input->id;
    }
}
