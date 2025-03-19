<?php

namespace Tempest\Mapper\Serializers;

use BackedEnum;
use Tempest\Mapper\Exceptions\CannotSerializeValue;
use Tempest\Mapper\Serializer;
use UnitEnum;

final class EnumSerializer implements Serializer
{
    public function serialize(mixed $input): string
    {
        if ($input instanceof BackedEnum) {
            return (string) $input->value;
        }

        if ($input instanceof UnitEnum) {
            return $input->name;
        }

        throw new CannotSerializeValue('enum');
    }
}
