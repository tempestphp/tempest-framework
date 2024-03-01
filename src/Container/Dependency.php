<?php

declare(strict_types=1);

namespace Tempest\Container;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

final readonly class Dependency
{
    public function __construct(
        public ReflectionParameter|ReflectionClass $reflector,
    ) {
    }

    public function getId(): string
    {
        return $this->typeToString($this->getType());
    }

    public function __toString(): string
    {
        $typeToString = $this->typeToString($this->getType());
        $parts = explode('\\', $typeToString);
        $typeToString = $parts[array_key_last($parts)];

        return implode(
            ' ',
            array_filter([
                $typeToString,
                '$' . $this->reflector->getName(),
            ]),
        );
    }

    private function getType(): string|ReflectionType
    {
        return match($this->reflector::class) {
            ReflectionParameter::class => $this->reflector->getType(),
            ReflectionClass::class => $this->reflector->getName(),
        };
    }

    private function typeToString(string|ReflectionType|null $type): ?string
    {
        if ($type === null) {
            return null;
        }

        if (is_string($type)) {
            return $type;
        }

        return match($type::class) {
            ReflectionIntersectionType::class => $this->intersectionTypeToString($type),
            ReflectionNamedType::class => $type->getName(),
            ReflectionUnionType::class => $this->unionTypeToString($type),
        };
    }

    private function intersectionTypeToString(ReflectionIntersectionType $type): string
    {
        return implode(
            '&',
            array_map(
                fn (ReflectionType $subType) => $this->typeToString($subType),
                $type->getTypes(),
            ),
        );
    }

    private function unionTypeToString(ReflectionUnionType $type): string
    {
        return implode(
            '|',
            array_map(
                fn (ReflectionType $subType) => $this->typeToString($subType),
                $type->getTypes(),
            ),
        );
    }
}
