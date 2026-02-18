<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Tempest\Core\Priority;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\FormatPattern;
use Tempest\Mapper\Caster;
use Tempest\Mapper\ConfigurableCaster;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicCaster;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Validation\Rules\HasDateTimeFormat;

#[Priority(Priority::HIGHEST)]
final readonly class DateTimeCaster implements Caster, DynamicCaster, ConfigurableCaster
{
    public function __construct(
        private FormatPattern|string $format = FormatPattern::ISO8601,
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(DateTimeInterface::class);
    }

    public static function configure(PropertyReflector $property, Context $context): self
    {
        return new self(
            format: $property->getAttribute(HasDateTimeFormat::class)->format ?? FormatPattern::ISO8601,
        );
    }

    public function cast(mixed $input): ?DateTimeInterface
    {
        if (! $input) {
            return null;
        }

        if ($input instanceof DateTimeInterface) {
            return $input;
        }

        try {
            if ($this->format !== FormatPattern::ISO8601 && is_string($input)) {
                return DateTime::fromPattern($input, $this->format);
            }

            return DateTime::parse($input);
        } catch (\Throwable) {
            return null;
        }
    }
}
