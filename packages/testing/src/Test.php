<?php

namespace Tempest\Testing;

use Attribute;
use ReflectionMethod;
use Tempest\Reflection\MethodReflector;

#[Attribute(Attribute::TARGET_METHOD)]
final class Test
{
    public MethodReflector $handler;

    public string $name {
        get => $this->handler->getDeclaringClass()->getName() . '::' . $this->handler->getName() . '"';
    }

    public static function fromName(string $name): self
    {
        $handler = new MethodReflector(new ReflectionMethod(...explode('::', $name)));

        $test = new self();
        $test->handler = $handler;

        return $test;
    }

    public function matchesFilter(string $filter): bool
    {
        return str_contains($this->name, $filter);
    }
}