<?php

declare(strict_types=1);

namespace Tempest\Container;

use Closure;
use Tempest\Container\Exceptions\CircularDependencyException;
use Tempest\Reflection\Reflector;

final class DependencyChain
{
    /**
     * @var \Tempest\Container\Dependency[]
     */
    private array $dependencies = [];

    public function __construct(
        private string $origin,
    ) {
    }

    public function add(Reflector|Closure|string $dependency): self
    {
        $dependency = new Dependency($dependency);

        if (isset($this->dependencies[$dependency->getName()])) {
            throw new CircularDependencyException($this, $dependency);
        }

        $this->dependencies[$dependency->getName()] = $dependency;

        return $this;
    }

    public function first(): Dependency
    {
        return $this->dependencies[array_key_first($this->dependencies)];
    }

    public function last(): Dependency
    {
        return $this->dependencies[array_key_last($this->dependencies)];
    }

    /** @return \Tempest\Container\Dependency[] */
    public function all(): array
    {
        return $this->dependencies;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function clone(): self
    {
        return clone $this;
    }
}
