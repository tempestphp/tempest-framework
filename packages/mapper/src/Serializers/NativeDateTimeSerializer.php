<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use DateTime as NativeDateTime;
use DateTimeImmutable as NativeDateTimeImmutable;
use DateTimeInterface;
use DateTimeInterface as NativeDateTimeInterface;
use Tempest\Core\Priority;
use Tempest\Mapper\ConfigurableSerializer;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Validation\Rules\HasDateTimeFormat;

#[Priority(Priority::HIGHEST)]
final readonly class NativeDateTimeSerializer implements Serializer, DynamicSerializer, ConfigurableSerializer
{
    public function __construct(
        private string $format = 'Y-m-d H:i:s',
    ) {}

    public static function accepts(PropertyReflector|TypeReflector $input): bool
    {
        $type = $input instanceof PropertyReflector
            ? $input->getType()
            : $input;

        return $type->matches(NativeDateTimeInterface::class) || $type->matches(NativeDateTimeImmutable::class) || $type->matches(NativeDateTime::class);
    }

    public static function configure(PropertyReflector|TypeReflector|string $input, Context $context): Serializer
    {
        if ($input instanceof PropertyReflector) {
            $format = $input->getAttribute(HasDateTimeFormat::class)->format ?? 'Y-m-d H:i:s';
        } else {
            $format = 'Y-m-d H:i:s';
        }

        return new self($format);
    }

    public function serialize(mixed $input): string
    {
        if (! $input instanceof DateTimeInterface) {
            throw new ValueCouldNotBeSerialized(DateTimeInterface::class);
        }

        return $input->format($this->format);
    }
}
