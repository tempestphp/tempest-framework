<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use DateTimeInterface as NativeDateTimeInterface;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\FormatPattern;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Validation\Rules\HasDateTimeFormat;

final readonly class DateTimeSerializer implements Serializer
{
    public function __construct(
        private FormatPattern|string $format = FormatPattern::SQL_DATE_TIME,
    ) {}

    public static function fromReflector(PropertyReflector|TypeReflector $reflector): self
    {
        if ($reflector instanceof PropertyReflector) {
            $format = $reflector->getAttribute(HasDateTimeFormat::class)->format ?? FormatPattern::SQL_DATE_TIME;
        } else {
            $format = FormatPattern::SQL_DATE_TIME;
        }

        return new self($format);
    }

    public function serialize(mixed $input): string
    {
        if ($input instanceof NativeDateTimeInterface) {
            $input = DateTime::parse($input);
        }

        if (! $input instanceof DateTimeInterface) {
            throw new ValueCouldNotBeSerialized(DateTimeInterface::class);
        }

        return $input->format($this->format);
    }
}
