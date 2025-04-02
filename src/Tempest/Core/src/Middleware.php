<?php

namespace Tempest\Core;

use ArrayIterator;
use IteratorAggregate;
use Traversable;

final class Middleware implements IteratorAggregate
{
    private(set) array $middlewareClasses;

    public function __construct(
        /** @var class-string $middlewareClasses */
        string ...$middlewareClasses,
    ) {
        foreach ($middlewareClasses as $middlewareClass) {
            $this->middlewareClasses[$middlewareClass] = $middlewareClass;
        }
    }

    public function removeMiddleware(string $middlewareClass): self
    {
        unset($this->middlewareClasses[$middlewareClass]);

        return $this;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->middlewareClasses);
    }
}