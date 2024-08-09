<?php

declare(strict_types=1);

namespace Tempest\Container;

use ReflectionClass;
use Tempest\Support\Reflection\ClassReflector;

interface Container
{
    public function register(string $className, callable $definition): self;

    public function singleton(string $className, object|callable $definition, ?string $tag = null): self;

    public function config(object $config): self;

    /**
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    public function get(string $className, ?string $tag = null, mixed ...$params): object;

    public function call(object $object, string $methodName, mixed ...$params): mixed;

    /**
     * @template T of \Tempest\Container\Initializer
     * @template U of \Tempest\Container\DynamicInitializer
     * @param ClassReflector|ReflectionClass<T>|class-string<T>|class-string<U> $initializerClass
     * @return self
     */
    public function addInitializer(ClassReflector|ReflectionClass|string $initializerClass): self;
}
