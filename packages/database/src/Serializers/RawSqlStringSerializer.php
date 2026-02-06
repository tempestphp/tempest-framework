<?php

declare(strict_types=1);

namespace Tempest\Database\Serializers;

use Stringable;
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
final class RawSqlStringSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->getName() === 'string' || $type->matches(Stringable::class);
    }

    public function serialize(mixed $input): string
    {
        if (! is_string($input) && ! $input instanceof Stringable) {
            throw new ValueCouldNotBeSerialized('string');
        }

        return "'" . str_replace("'", "''", (string) $input) . "'";
    }
}
