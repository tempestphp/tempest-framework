<?php

declare(strict_types=1);

namespace Tempest\ORM\Mappers;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use Tempest\Interfaces\Caster;
use Tempest\Interfaces\Mapper;
use Tempest\ORM\Attributes\CastWith;
use Tempest\ORM\Attributes\Lazy;
use Tempest\ORM\Exceptions\MissingValuesException;

final readonly class ArrayMapper implements Mapper
{
    public function canMap(mixed $data): bool
    {
        return is_array($data);
    }

    public function map(string $className, mixed $data): array|object
    {
        $class = new ReflectionClass($className);

        $object = $class->newInstanceWithoutConstructor();

        $reflectionProperties = $class->getProperties(ReflectionProperty::IS_PUBLIC);

        $missingValues = [];

        foreach ($reflectionProperties as $property) {
            $propertyName = $property->getName();

            if (! array_key_exists($propertyName, $data)) {
                if ($property->getAttributes(Lazy::class) === []) {
                    $missingValues[] = $propertyName;
                }

                continue;
            }

            if ($targetClass = $this->getTargetClass($property)) {
                $item = $data[$propertyName];

                $caster = $this->getCaster($property);

                if (! is_array($item)) {
                    $value = $caster?->cast($item) ?? $item;
                } else {
                    $input = [
                        lcfirst($class->getShortName()) => $object, // Inverse 1:1 relation, if present
                        lcfirst($class->getShortName()) . 's' => [$object], // Inverse 1:n relation, if present
                        ...$data[$propertyName],
                    ];

                    $value = $this->map($targetClass, $caster?->cast($input) ?? $input);
                }
            } elseif ($targetClass = $this->getTargetClassForArray($property)) {
                $value = array_map(
                    function (array|object $item) use ($propertyName, $property, $targetClass, $class, $object) {
                        $caster = $this->getCaster($property);

                        if (! is_array($item)) {
                            $input = $item;

                            return $caster?->cast($input) ?? $input;
                        } else {
                            $input = [
                                lcfirst($class->getShortName()) => $object, // Inverse 1:1 relation, if present
                                lcfirst($class->getShortName()) . 's' => [$object], // Inverse 1:n relation, if present
                                ...$item,
                            ];

                            return $this->map($targetClass, $caster?->cast($input) ?? $input);
                        }
                    },
                    $data[$propertyName],
                );
            } else {
                $caster = $this->getCaster($property);

                $value = $caster?->cast($data[$propertyName]) ?? $data[$propertyName];
            }

            $property->setValue($object, $value);
        }

        if ($missingValues !== []) {
            throw new MissingValuesException($className, $missingValues);
        }

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

    private function getTargetClassForArray(ReflectionProperty $property): ?string
    {
        $doc = $property->getDocComment();

        if (! $doc) {
            return null;
        }

        preg_match('/@var ([\\\\\w]+)\[]/', $doc, $match);

        return $match[1] ?? null;
    }

    private function getTargetClass(ReflectionProperty $property): ?string
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
}
