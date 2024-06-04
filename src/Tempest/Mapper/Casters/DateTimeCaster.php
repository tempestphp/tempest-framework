<?php

declare(strict_types=1);

namespace Tempest\Mapper\Casters;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use ReflectionProperty;
use Tempest\Mapper\Caster;
use Tempest\Validation\Rules\DateTimeFormat;
use function Tempest\attribute;
use function Tempest\type;

final readonly class DateTimeCaster implements Caster
{
    public function __construct(
        private string $format = 'Y-m-d H:i:s',
        private bool $immutable = true,
    ) {
    }

    public static function fromProperty(ReflectionProperty $property)
    {
        $format = attribute(DateTimeFormat::class)
            ->in($property)
            ->first()
            ?->format ?? 'Y-m-d H:i:s';

        return match (type($property)) {
            DateTime::class => new DateTimeCaster($format, immutable: false),
            default => new DateTimeCaster($format, immutable: true),
        };
    }

    public function cast(mixed $input): DateTimeInterface
    {
        if ($this->immutable) {
            $date = DateTimeImmutable::createFromFormat($this->format, $input);
        } else {
            $date = DateTime::createFromFormat($this->format, $input);
        }

        if (! $date) {
            throw new InvalidArgumentException("Must be a valid date in the format {$this->format}");
        }

        return $date;
    }
}
