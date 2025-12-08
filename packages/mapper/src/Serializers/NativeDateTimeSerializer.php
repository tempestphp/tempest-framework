<?php

declare(strict_types=1);

namespace Tempest\Mapper\Serializers;

use DateTime as NativeDateTime;
use DateTimeImmutable as NativeDateTimeImmutable;
use DateTimeInterface;
use DateTimeInterface as NativeDateTimeInterface;
use Tempest\Core\Priority;
use Tempest\Mapper\Context;
use Tempest\Mapper\DynamicSerializer;
use Tempest\Mapper\Exceptions\ValueCouldNotBeSerialized;
use Tempest\Mapper\Serializer;
use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;
use Tempest\Validation\Rules\HasDateTimeFormat;

#[Context(Context::DEFAULT)]
#[Priority(Priority::HIGHEST)]
final readonly class NativeDateTimeSerializer implements Serializer, DynamicSerializer
{
    public function __construct(
        private string $format = 'Y-m-d H:i:s',
    ) {}

    public static function for(): array
    {
        return [NativeDateTimeInterface::class, NativeDateTimeImmutable::class, NativeDateTime::class];
    }

    public static function make(PropertyReflector|TypeReflector|string $input): Serializer
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
