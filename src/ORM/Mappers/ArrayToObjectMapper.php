<?php

declare(strict_types=1);

namespace Tempest\ORM\Mappers;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

use function Tempest\get;

use Tempest\Interfaces\Caster;
use Tempest\Interfaces\IsValidated;
use Tempest\Interfaces\Mapper;
use Tempest\ORM\Attributes\CastWith;
use Tempest\ORM\Exceptions\MissingValuesException;
use Tempest\Validation\Validator;

final readonly class ArrayToObjectMapper implements Mapper
{
    public function canMap(object|string $objectOrClass, mixed $data): bool
    {
        return is_array($data);
    }

    public function map(object|string $objectOrClass, mixed $data): array|object
    {
        $object = $this->resolveObject($objectOrClass);

        $class = new ReflectionClass($objectOrClass);

        $missingValues = [];

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propertyName = $property->getName();

            if (! array_key_exists($propertyName, $data)) {
                if (! $this->hasDefaultValue($property)) {
                    $missingValues[] = $propertyName;
                }

                continue;
            }

            $value = $this->resolveValueFromType(
                data: $data[$propertyName],
                property: $property,
                parent: $object,
            );

            if ($value instanceof UnknownValue) {
                $value = $this->resolveValueFromArray(
                    data: $data[$propertyName],
                    property: $property,
                    parent: $object,
                );
            }

            if ($value instanceof UnknownValue) {
                $caster = $this->getCaster($property);

                $value = $caster?->cast($data[$propertyName]) ?? $data[$propertyName];
            }

            $property->setValue($object, $value);
        }

        if ($missingValues !== []) {
            throw new MissingValuesException($objectOrClass, $missingValues);
        }

        $this->validate($object);

        return $object;
    }

    private function getCaster(ReflectionProperty $property): ?Caster
    {
        /** @var \Tempest\ORM\Attributes\CastWith|null $attribute */
        $attribute = ($property->getAttributes(CastWith::class)[0] ?? null)?->newInstance();

        if (! $attribute) {
            try {
                $class = new ReflectionClass($property->getType()->getName());
                $attribute = ($class->getAttributes(CastWith::class)[0] ?? null)?->newInstance();
            } catch (ReflectionException) {
                return null;
            }
        }

        if (! $attribute) {
            return null;
        }

        return get($attribute->className);
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

        return $this->map($type, $caster?->cast($input) ?? $input);
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
                objectOrClass: $type,
                data: $caster?->cast($input) ?? $input,
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
        if (! $object instanceof IsValidated) {
            return;
        }

        $validator = new Validator();

        $validator->validate($object);
    }
}
