<?php

declare(strict_types=1);

namespace Tempest\Database\Serializers;

use DateTimeInterface as NativeDateTimeInterface;
use Tempest\Core\Priority;
use Tempest\Database\RawSqlDatabaseContext;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\FormatPattern;
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

#[Priority(Priority::HIGH)]
#[Context(RawSqlDatabaseContext::class)]
final class RawSqlDateTimeSerializer implements Serializer, DynamicSerializer
{
    public static function accepts(PropertyReflector|TypeReflector $type): bool
    {
        $type = $type instanceof PropertyReflector
            ? $type->getType()
            : $type;

        return $type->matches(DateTime::class) || $type->matches(DateTimeInterface::class) || $type->matches(NativeDateTimeInterface::class);
    }

    public function serialize(mixed $input): string
    {
        if ($input instanceof NativeDateTimeInterface) {
            $input = DateTime::parse($input);
        }

        if (! $input instanceof DateTimeInterface) {
            throw new ValueCouldNotBeSerialized(DateTimeInterface::class);
        }

        return "'" . $input->format(FormatPattern::SQL_DATE_TIME) . "'";
    }
}
