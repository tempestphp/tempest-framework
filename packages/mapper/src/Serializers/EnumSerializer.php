<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use BackedEnum;
use Tempest\Core\Priority;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use UnitEnum;

#[Priority(Priority::NORMAL)]
final class EnumSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(UnitEnum::class);
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
