<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use Closure;
use Tempest\Core\Priority;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\FormatPattern;
use Tempest\Mapper\Caster;
use Tempest\Mapper\ConfigurableCaster;
use Tempest\Mapper\Context;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Validation\Rules\HasDateTimeFormat;

#[Priority(Priority::HIGHEST)]
final readonly class DateTimeCaster implements Caster, ConfigurableCaster
{
    public function __construct(
        private FormatPattern|string $format = FormatPattern::ISO8601,
    ) {}

    public static function for(): Closure
    {
        return fn (TypeReflector $type) => $type->matches(DateTimeInterface::class);
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
            return DateTime::parse($input);
        } catch (\Throwable) {
            return null;
        }
    }
}
