<?php

declare(strict_types=1);

namespace Tempest\Container;

use Exception;
use ReflectionParameter;
use Tempest\Container\Exceptions\CircularDependencyException;
use Throwable;

final class InMemoryContainerLog implements ContainerLog
{
    private string $origin = '';

    public function __construct(
        /** @var \Tempest\Container\Context[] $stack */
        public array $stack = [],
    ) {}

    public function startResolving(): ContainerLog
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $this->origin = $trace[1]['file'] . ':' . $trace[1]['line'];
        $this->stack = [];

        return $this;
    }

    public function addContext(Context $context): ContainerLog
    {
        if (isset($this->stack[$context->getName()])) {
            throw new CircularDependencyException($this, $context);
        }

        $this->stack[$context->getName()] = $context;

        return $this;
    }

    public function addDependency(Dependency $dependency): ContainerLog
    {
        if ($this->stack === []) {
            $reflector = $dependency->reflector;

            if ($reflector instanceof ReflectionParameter) {
                $reflector = $reflector->getDeclaringClass();
            }

            $this->addContext(new Context($reflector));
        }

        $this->currentContext()->addDependency($dependency);

        return $this;
    }

    public function getStack(): array
    {
        return $this->stack;
    }

    public function currentContext(): Context
    {
        return $this->stack[array_key_last($this->stack)]
            ?? throw new Exception("No current context found. That shoudn't happen. Aidan probably wrote a bug somewhere.");
    }

    public function currentDependency(): ?Dependency
    {
        return $this->currentContext()->currentDependency();
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }
}
