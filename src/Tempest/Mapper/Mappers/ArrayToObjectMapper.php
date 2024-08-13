<?php

declare(strict_types=1);

namespace Tempest\Mapper\Mappers;

use BackedEnum;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use ReflectionClass;
use ReflectionException;
use function Tempest\get;
use Tempest\Mapper\Caster;
use Tempest\Mapper\Casters\BooleanCaster;
use Tempest\Mapper\Casters\DateTimeCaster;
use Tempest\Mapper\Casters\EnumCaster;
use Tempest\Mapper\Casters\FloatCaster;
use Tempest\Mapper\Casters\IntegerCaster;
use Tempest\Mapper\CastWith;
use Tempest\Mapper\Exceptions\MissingValuesException;
use Tempest\Mapper\Mapper;
use Tempest\Mapper\Strict;
use Tempest\Mapper\UnknownValue;
use Tempest\Support\ArrayHelper;
use Tempest\Support\Reflection\ClassReflector;
use Tempest\Support\Reflection\PropertyReflector;
use Tempest\Validation\Validator;
use Throwable;

final readonly class ArrayToObjectMapper implements Mapper
{
    public function canMap(mixed $from, mixed $to): bool
    {
        if (! is_array($from)) {
            return false;
        }

        try {
            $class = new ClassReflector($to);

            return $class->isInstantiable();
        } catch (Throwable) {
            return false;
        }
    }

    public function map(mixed $from, mixed $to): object
    {
        $object = $this->resolveObject($to);

        $class = new ClassReflector($to);

        $missingValues = [];
        $unsetProperties = [];

        $from = (new ArrayHelper())->unwrap($from);

        $isStrictClass = $class->hasAttribute(Strict::class);

        foreach ($class->getPublicProperties() as $property) {
            $propertyName = $property->getName();

            if (! array_key_exists($propertyName, $from)) {
                $isStrictProperty = $isStrictClass || $property->hasAttribute(Strict::class);

                if ($property->hasDefaultValue()) {
                    continue;
                }

                if ($isStrictProperty) {
                    $missingValues[] = $propertyName;
                } else {
                    $unsetProperties[] = $propertyName;
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

        // Non-strict properties that weren't passed are unset,
        // which means that they can now be accessed via `__get`
        foreach ($unsetProperties as $unsetProperty) {
            unset($object->{$unsetProperty});
        }

        $this->validate($object);

        return $object;
    }

    private function getCaster(PropertyReflector $property): ?Caster
    {
        // Get CastWith from the property
        $castWith = $property->getAttribute(CastWith::class);

        $type = $property->getType();

        // Get CastWith from the property's type
        if (! $castWith) {
            try {
                $castWith = $type->asClass()->getAttribute(CastWith::class);
            } catch (ReflectionException) {
                // Could not resolve CastWith from the type
            }
        }

        if ($castWith) {
            // Resolve the caster from the container
            return get($castWith->className);
        }

        // Check if backed enum
        if ($type->matches(BackedEnum::class)) {
            return new EnumCaster($type->getName());
        }

        // Get Caster from built-in casters
        return match ($type->getName()) {
            'int' => new IntegerCaster(),
            'float' => new FloatCaster(),
            'bool' => new BooleanCaster(),
            DateTimeImmutable::class, DateTimeInterface::class, DateTime::class => DateTimeCaster::fromProperty($property),
            default => null,
        };
    }

    private function resolveObject(mixed $objectOrClass): object
    {
        if (is_object($objectOrClass)) {
            return $objectOrClass;
        }

        return (new ReflectionClass($objectOrClass))->newInstanceWithoutConstructor();
    }

    private function resolveValueFromType(
        mixed $data,
        PropertyReflector $property,
        object $parent,
    ): mixed {
        $type = $property->getType();

        if ($type === null || $type->isBuiltIn()) {
            return new UnknownValue();
        }

        $caster = $this->getCaster($property);

        if (! is_array($data)) {
            return $caster?->cast($data) ?? $data;
        }

        $data = $this->withParentRelations(
            $type->asClass(),
            $parent,
            $data,
        );

        return $this->map(
            from: $caster?->cast($data) ?? $data,
            to: $type->getName(),
        );
    }

    private function resolveValueFromArray(
        mixed $data,
        PropertyReflector $property,
        object $parent,
    ): UnknownValue|array {
        $type = $property->getIterableType();

        if (! $type) {
            return new UnknownValue();
        }

        $values = [];

        $caster = $this->getCaster($property);

        foreach ($data as $item) {
            if (! is_array($item)) {
                $values[] = $caster?->cast($item) ?? $item;

                continue;
            }

            $item = $this->withParentRelations(
                $type->asClass(),
                $parent,
                $item,
            );

            $values[] = $this->map(
                from: $caster?->cast($item) ?? $item,
                to: $type->getName(),
            );
        }

        return $values;
    }

    private function validate(mixed $object): void
    {
        $validator = new Validator();

        $validator->validate($object);
    }

    private function withParentRelations(
        ClassReflector $child,
        object $parent,
        array $data,
    ): array {
        foreach ($child->getPublicProperties() as $property) {
            if ($property->getType()->getName() === $parent::class) {
                $data[$property->getName()] = $parent;
            }

            if ($property->getIterableType()?->getName() === $parent::class) {
                $data[$property->getName()] = [$parent];
            }
        }

        return $data;
    }
}
