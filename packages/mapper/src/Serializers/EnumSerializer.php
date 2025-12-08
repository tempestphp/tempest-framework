<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use BackedEnum;
use Tempest\Core\Priority;
use Tempest\Mapper\Context;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use UnitEnum;

#[Context(Context::DEFAULT)]
#[Priority(Priority::NORMAL)]
final class EnumSerializer implements Serializer
{
    public static function for(): string
    {
        return UnitEnum::class;
    }

    public function serialize(mixed $input): string
    {
        if ($input instanceof BackedEnum) {
            return (string) $input->value;
        }

        if ($input instanceof UnitEnum) {
            return $input->name;
        }

        throw new ValueCouldNotBeSerialized('enum');
    }
}
