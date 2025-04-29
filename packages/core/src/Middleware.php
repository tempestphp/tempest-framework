<?php

namespace Tempest\Core;

use ArrayIterator;
use Generator;
use IteratorAggregate;
use Tempest\Reflection\ClassReflector;
use Traversable;

use function Tempest\Support\arr;

/** @template MiddlewareInterface */
final class Middleware implements IteratorAggregate
{
    private(set) array $middlewareClasses = [];

    public function __construct(
        /** @var class-string<MiddlewareInterface> ...$middlewareClasses */
        string ...$middlewareClasses,
    ) {
        $this->add(...$middlewareClasses);
    }

    public function clone(): self
    {
        return clone $this;
    }

    /** @param class-string<MiddlewareInterface> ...$middlewareClasses */
    public function add(string ...$middlewareClasses): self
    {
        foreach ($middlewareClasses as $middlewareClass) {
            $this->middlewareClasses[$middlewareClass] = new ClassReflector($middlewareClass);
        }

        return $this->sort();
    }

    /** @param class-string<MiddlewareInterface> ...$middlewareClasses */
    public function remove(string ...$middlewareClasses): self
    {
        foreach ($middlewareClasses as $middlewareClass) {
            unset($this->middlewareClasses[$middlewareClass]);
        }

        return $this->sort();
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->middlewareClasses);
    }

    public function unwrap(): Generator
    {
        $stack = $this->middlewareClasses;

        while ($middlewareClass = array_pop($stack)) {
            yield $middlewareClass->getName() => $middlewareClass;
        }
    }

    /** @return self<MiddlewareInterface> */
    private function sort(): self
    {
        uasort($this->middlewareClasses, function (ClassReflector $a, ClassReflector $b): int {
            $priorityA = $a->getAttribute(Priority::class)->priority ?? Priority::NORMAL;
            $priorityB = $b->getAttribute(Priority::class)->priority ?? Priority::NORMAL;

            return $priorityA <=> $priorityB;
        });

        return $this;
    }

    public function __serialize(): array
    {
        return [
            'middlewareClasses' => arr($this->middlewareClasses)
                ->map(fn (ClassReflector $class) => $class->getName())
                ->toArray(),
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->middlewareClasses = arr($data['middlewareClasses'])
            ->map(fn (string $className) => new ClassReflector($className))
            ->toArray();
    }
}
