<?php

declare(strict_types=1);

namespace Tempest\Support\Reflection;

use Closure;
use Generator;
use ReflectionFunction as PHPReflectionFunction;

final readonly class FunctionReflector implements Reflector
{
    private PHPReflectionFunction $reflectionFunction;

    public function __construct(
        PHPReflectionFunction|Closure $function
    ) {
        $this->reflectionFunction = $function instanceof Closure
            ? new PHPReflectionFunction($function)
            : $function;
    }

    /** @return Generator|\Tempest\Support\Reflection\ParameterReflector[] */
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
