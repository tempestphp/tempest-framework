<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use DateTimeInterface as PhpDateTimeInterface;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface as TempestDateTimeInterface;
use Tempest\DateTime\FormatPattern;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Validation\Rules\DateTimeFormat;

final readonly class DateTimeSerializer implements Serializer
{
    public function __construct(
        private FormatPattern|string|null $format = null,
    ) {}

    public static function fromProperty(PropertyReflector $property): self
    {
        $format = $property->getAttribute(DateTimeFormat::class)->format ?? null;

        return new self($format);
    }

    public function serialize(mixed $input): string
    {
        if ($input instanceof PhpDateTimeInterface) {
            $input = DateTime::parse($input);
        }

        if (! ($input instanceof TempestDateTimeInterface)) {
            throw new ValueCouldNotBeSerialized(TempestDateTimeInterface::class);
        }

        $format = $this->format;

        if ($format === null) {
            $format = match(true) {
                $input instanceof TempestDateTimeInterface => FormatPattern::SQL_DATE_TIME,
                default => 'Y-m-d H:i:s',
            };
        }

        return $input->format($format);
    }
}
