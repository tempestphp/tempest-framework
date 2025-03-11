<?php

declare(strict_types=1);

namespace Tempest\Reflection;

use Closure;
use Generator;
use ReflectionFunction as PHPReflectionFunction;

final readonly class FunctionReflector implements Reflector
{
    private PHPReflectionFunction $reflectionFunction;

    public function __construct(PHPReflectionFunction|Closure $function)
    {
        $this->reflectionFunction = ($function instanceof Closure)
            ? new PHPReflectionFunction($function)
            : $function;
    }

    public function invokeArgs(array $args = []): mixed
    {
        return $this->reflectionFunction->invokeArgs($args);
    }

    /** @return Generator|\Tempest\Reflection\ParameterReflector[] */
    public function getParameters(): Generator
    {
        foreach ($this->reflectionFunction->getParameters() as $parameter) {
            yield new ParameterReflector($parameter);
        }
    }

    public function getName(): string
    {
        return $this->reflectionFunction->getName();
    }

    public function getShortName(): string
    {
        return $this->reflectionFunction->getShortName();
    }

    public function getFileName(): string
    {
        return $this->reflectionFunction->getFileName();
    }

    public function getStartLine(): int
    {
        return (int) $this->reflectionFunction->getStartLine();
    }
}
