<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;

interface Container
{
    public function register(string $className, callable $definition): self;

    public function unregister(string $className, bool $tagged = false): self;

    public function singleton(string $className, mixed $definition, ?string $tag = null): self;

    public function config(object $config): self;

    /**
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @return null|TClassName
     */
    public function get(string $className, ?string $tag = null, mixed ...$params): mixed;

    public function has(string $className, ?string $tag = null): bool;

    public function invoke(ClassReflector|MethodReflector|FunctionReflector|callable|string $method, mixed ...$params): mixed;

    /**
     * @template T of \Tempest\Container\Initializer
     * @template U of \Tempest\Container\DynamicInitializer
     * @param ClassReflector<T>|class-string<T>|class-string<U> $initializerClass
     */
    public function addInitializer(ClassReflector|string $initializerClass): self;
}
