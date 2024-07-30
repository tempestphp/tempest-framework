<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use Generator;
use ReflectionMethod;

final readonly class MethodReflector implements Reflector
{
    public function __construct(
        private ReflectionMethod $reflectionMethod,
    ) {
    }

    public function getReflection(): ReflectionMethod
    {
        return $this->reflectionMethod;
    }

    /** @return Generator|\Tempest\Support\Reflection\ParameterReflector[] */
    public function getParameters(): Generator
    {
        foreach ($this->reflectionMethod->getParameters() as $parameter) {
            yield new ParameterReflector($parameter);
        }
    }

    public function invokeArgs(object|null $object, array $args = []): mixed
    {
        return $this->reflectionMethod->invokeArgs($object, $args);
    }

    public function getName(): string
    {
        return $this->getName();
    }
}
