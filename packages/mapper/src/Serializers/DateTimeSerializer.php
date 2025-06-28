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
use Tempest\Validation\Rules\DateTimeFormat;

final readonly class DateTimeSerializer implements Serializer
{
    public function __construct(
        private FormatPattern|string $format = FormatPattern::ISO8601,
    ) {}

    public static function fromProperty(PropertyReflector $property): self
    {
        $format = $property->getAttribute(DateTimeFormat::class)->format ?? FormatPattern::ISO8601;

        return new self($format);
    }

    public function serialize(mixed $input): string
    {
        if ($input instanceof NativeDateTimeInterface) {
            $input = DateTime::parse($input);
        }

        if (! ($input instanceof DateTimeInterface)) {
            throw new ValueCouldNotBeSerialized(DateTimeInterface::class);
        }

        return $input->format($this->format);
    }
}
