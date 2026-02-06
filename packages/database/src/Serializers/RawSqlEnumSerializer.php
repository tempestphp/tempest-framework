<?php

declare(strict_types=1);

namespace Tempest\Database\Serializers;

use BackedEnum;
use Tempest\Core\Priority;
use Tempest\Database\RawSqlDatabaseContext;
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use UnitEnum;

#[Priority(Priority::NORMAL)]
#[Context(RawSqlDatabaseContext::class)]
final class RawSqlEnumSerializer implements Serializer, DynamicSerializer
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
            return sprintf('"%s"', $input->value);
        }

        if ($input instanceof UnitEnum) {
            return sprintf('"%s"', $input->name);
        }

        throw new ValueCouldNotBeSerialized('enum');
    }
}
