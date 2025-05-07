<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Support;

use AssertionError;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;

trait GeneratesArguments
{
    private function assertAllMethods(string $class, Closure $callback, array $except = []): void
    {
        $hasExecutedCallback = false;
        $reflectionClass = new ReflectionClass($class);
        $reflectionMethods = $reflectionClass->getMethods();

        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();

            if ($reflectionMethod->isStatic()) {
                continue;
            }

            if ($reflectionMethod->getReturnType() instanceof ReflectionUnionType) {
                continue;
            }

            /** @phpstan-ignore-next-line method.notFound */
            if ($reflectionMethod->getReturnType()?->getName() !== 'self') {
                continue;
            }

            if (! $reflectionMethod->isPublic()) {
                continue;
            }

            if (in_array($reflectionMethod->getName(), $except, strict: true)) {
                continue;
            }

            $arguments = array_map(
                callback: fn (ReflectionParameter $parameter) => $parameter->isDefaultValueAvailable()
                    ? $parameter->getDefaultValue()
                    : $this->generateArgument($parameter->getType()), // @phpstan-ignore-line argument.type doesn't take inheritance into account apparently
                array: $reflectionMethod->getParameters(),
            );

            $expected = new $class('tempest');
            $actual = $expected->$methodName(...$arguments);

            $this->assertInstanceOf($class, $actual);

            $callback($expected, $actual, $methodName, $arguments);
            $hasExecutedCallback = true;
        }

        if (! $hasExecutedCallback) {
            throw new AssertionError('None of the methods were called.');
        }
    }

    private function generateArgument(ReflectionNamedType|ReflectionUnionType|ReflectionIntersectionType|null $type): mixed
    {
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $item) {
                if (! is_null($value = $this->generateArgument($item))) {
                    return $value;
                }
            }

            throw new Exception("No valid type found in union type [{$type->__toString()}].");
        }

        return match ($type->getName()) {
            'string' => 'tempest',
            'int' => 2,
            'iterable' => [],
            'array' => [],
            'bool' => true,
            default => null,
        };
    }
}
