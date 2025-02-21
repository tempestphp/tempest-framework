<?php

namespace Tempest\Mapper\Serializers;

use BackedEnum;
use Tempest\Mapper\Serializer;
use UnitEnum;

class EnumSerializer implements Serializer
{
    public function serialize(mixed $input): string|null
    {
        if ($input instanceof BackedEnum) {
            return (string) $input->value;
        }

        if ($input instanceof UnitEnum) {
            return $input->name;
        }

        return null;
    }
}
