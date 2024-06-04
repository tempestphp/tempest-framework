<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Reflector;

final readonly class TypeName
{
    public function resolve(Reflector|ReflectionType $reflector): string
    {
        if (
            $reflector instanceof ReflectionParameter
            || $reflector instanceof ReflectionProperty
        ) {
            return $this->resolve($reflector->getType());
        }

        if ($reflector instanceof ReflectionClass) {
            return $reflector->getName();
        }

        if ($reflector instanceof ReflectionNamedType) {
            return $reflector->getName();
        }

        if ($reflector instanceof ReflectionUnionType) {
            return implode('|', array_map(
                fn (ReflectionType $reflectionType) => $this->resolve($reflectionType),
                $reflector->getTypes(),
            ));
        }

        if ($reflector instanceof ReflectionIntersectionType) {
            return implode('&', array_map(
                fn (ReflectionType $reflectionType) => $this->resolve($reflectionType),
                $reflector->getTypes(),
            ));
        }

        throw new Exception('Could not resolve type');
    }
}
