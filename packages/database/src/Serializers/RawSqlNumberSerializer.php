<?php

declare(strict_types=1);

namespace Tempest\Database\Serializers;

use Tempest\Core\Priority;
use Tempest\Database\RawSqlDatabaseContext;
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::NORMAL)]
#[Context(RawSqlDatabaseContext::class)]
final class RawSqlNumberSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return in_array($type->getName(), ['int', 'integer', 'float', 'double'], strict: true);
    }

    public function serialize(mixed $input): string
    {
        if (! is_int($input) && ! is_float($input)) {
            throw new ValueCouldNotBeSerialized('integer or float');
        }

        return (string) $input;
    }
}
