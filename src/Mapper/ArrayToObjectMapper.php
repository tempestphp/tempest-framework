<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use function Tempest\attribute;
use function Tempest\get;
use Tempest\ORM\Attributes\CastWith;
use Tempest\ORM\Caster;
use Tempest\ORM\Casters\BooleanCaster;
use Tempest\ORM\Casters\DateTimeCaster;
use Tempest\ORM\Casters\DateTimeImmutableCaster;
use Tempest\ORM\Casters\FloatCaster;
use Tempest\ORM\Casters\IntegerCaster;
use Tempest\ORM\Exceptions\MissingValuesException;
use Tempest\Support\ArrayHelper;
use Tempest\Validation\Rules\DateTimeFormat;
use Tempest\Validation\Validator;
use Throwable;

final readonly class ArrayToObjectMapper implements Mapper
{
    public function canMap(mixed $from, object|string $to): bool
    {
        return is_array($from);
    }

    public function map(mixed $from, object|string $to): array|object
    {
        $object = $this->resolveObject($to);

        $class = new ReflectionClass($to);

        $missingValues = [];

        $from = (new ArrayHelper())->unwrap($from);

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propertyName = $property->getName();

            if (! array_key_exists($propertyName, $from)) {
                if (! $this->hasDefaultValue($property)) {
                    $missingValues[] = $propertyName;
                }

                continue;
            }

            $value = $this->resolveValueFromType(
                data: $from[$propertyName],
                property: $property,
                parent: $object,
            );

            if ($value instanceof UnknownValue) {
                $value = $this->resolveValueFromArray(
                    data: $from[$propertyName],
                    property: $property,
                    parent: $object,
                );
            }

            if ($value instanceof UnknownValue) {
                $caster = $this->getCaster($property);

                $value = $caster?->cast($from[$propertyName]) ?? $from[$propertyName];
            }

            $property->setValue($object, $value);
        }

        if ($missingValues !== []) {
            throw new MissingValuesException($to, $missingValues);
        }

        $this->validate($object);

        return $object;
    }

    private function getCaster(ReflectionProperty $property): ?Caster
    {
        $castWith = attribute(CastWith::class)
            ->in($property)
            ->first();

        if (! $castWith) {
            try {
                $castWith = attribute(CastWith::class)
                    ->in($property->getType()->getName())
                    ->first();
            } catch (ReflectionException) {
            }
        }

        if (! $castWith) {
            return match($type = $property->getType()->getName()) {
                'int' => new IntegerCaster(),
                'float' => new FloatCaster(),
                'bool' => new BooleanCaster(),
                DateTimeImmutable::class, DateTime::class, DateTimeInterface::class => $this->createDateCaster($type, $property),
                default => null,
            };
        }

        return get($castWith->className);
    }

    private function resolveObject(object|string $objectOrClass): object
    {
        if (is_object($objectOrClass)) {
            return $objectOrClass;
        }

        return (new ReflectionClass($objectOrClass))->newInstanceWithoutConstructor();
    }

    private function resolveValueFromType(
        mixed $data,
        ReflectionProperty $property,
        object $parent,
    ): mixed {
        $type = $this->resolveType($property);

        if (! $type) {
            return new UnknownValue();
        }

        $item = $data;

        $caster = $this->getCaster($property);

        if (! is_array($item)) {
            return $caster?->cast($item) ?? $item;
        }

        $class = $property->getDeclaringClass();

        $input = [
            lcfirst($class->getShortName()) => $parent, // Inverse 1:1 relation, if present
            lcfirst($class->getShortName()) . 's' => [$parent], // Inverse 1:n relation, if present
            ...$data,
        ];

        return $this->map(
            from: $caster?->cast($input) ?? $input,
            to: $type,
        );
    }

    private function resolveValueFromArray(
        mixed $data,
        ReflectionProperty $property,
        object $parent,
    ): mixed {
        $type = $this->resolveTypeForArray($property);

        if (! $type) {
            return new UnknownValue();
        }

        $values = [];

        $class = $property->getDeclaringClass();

        $caster = $this->getCaster($property);

        foreach ($data as $item) {
            if (! is_array($item)) {
                $values[] = $caster?->cast($item) ?? $item;

                continue;
            }

            $input = [
                lcfirst($class->getShortName()) => $parent, // Inverse 1:1 relation, if present
                lcfirst($class->getShortName()) . 's' => [$parent], // Inverse 1:n relation, if present
                ...$item,
            ];

            $values[] = $this->map(
                from: $caster?->cast($input) ?? $input,
                to: $type,
            );
        }

        return $values;
    }

    private function resolveType(ReflectionProperty $property): ?string
    {
        $type = $property->getType();

        if (! $type) {
            return null;
        }

        if ($type->isBuiltin()) {
            return null;
        }

        return $type->getName();
    }

    private function resolveTypeForArray(ReflectionProperty $property): ?string
    {
        $doc = $property->getDocComment();

        if (! $doc) {
            return null;
        }

        preg_match('/@var ([\\\\\w]+)\[]/', $doc, $match);

        return $match[1] ?? null;
    }

    private function hasDefaultValue(ReflectionProperty $property): bool
    {
        $constructorParameters = [];

        foreach (($property->getDeclaringClass()->getConstructor()?->getParameters() ?? []) as $parameter) {
            $constructorParameters[$parameter->getName()] = $parameter;
        }

        $hasDefaultValue = $property->hasDefaultValue();

        $hasPromotedDefaultValue = $property->isPromoted()
            && $constructorParameters[$property->getName()]->isDefaultValueAvailable();

        return $hasDefaultValue || $hasPromotedDefaultValue;
    }

    private function validate(object|string $object): void
    {
        $validator = new Validator();

        $validator->validate($object);
    }

    /**
     * @param class-string<DateTime|DateTimeImmutable|DateTimeInterface> $type
     * @param ReflectionProperty $property
     *
     * @return Caster
     */
    private function createDateCaster(string $type, ReflectionProperty $property): Caster
    {
        $format = null;

        try {
            $format = attribute(DateTimeFormat::class)
                ->in($property)
                ->first();
        } catch (Throwable) {

        }

        return match ($type) {
            DateTime::class => new DateTimeCaster($format?->format),
            default => new DateTimeImmutableCaster($format->format),
        };
    }
}
