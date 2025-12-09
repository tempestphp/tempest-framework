<?php

declare(strict_types=1);

namespace Tempest\Database\Serializers;

use DateTimeInterface as NativeDateTimeInterface;
use Tempest\Database\DatabaseContext;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\FormatPattern;
use Tempest\Mapper\Attributes\Context;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;

#[Context(DatabaseContext::class)]
final readonly class DateTimeSerializer implements Serializer
{
    public static function for(): array
    {
        return [DateTime::class, DateTimeInterface::class, NativeDateTimeInterface::class];
    }

    public function serialize(mixed $input): string
    {
        if ($input instanceof NativeDateTimeInterface) {
            $input = DateTime::parse($input);
        }

        if (! $input instanceof DateTimeInterface) {
            throw new ValueCouldNotBeSerialized(DateTimeInterface::class);
        }

        return $input->format(FormatPattern::SQL_DATE_TIME);
    }
}
