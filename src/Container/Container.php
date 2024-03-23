<?php

declare(strict_types=1);

namespace Tempest\Container;

use Psr\Container\ContainerInterface;
use ReflectionClass;

interface Container extends ContainerInterface
{
    public function register(string $className, callable $definition): self;

    public function singleton(string $className, callable $definition): self;

    public function config(object $config): self;

    /**
     * @template TClassName
     * @param class-string<TClassName> $className
     *
     * @return TClassName
     */
    public function get(string $className, mixed ...$params): object;

    /**
     * @template TClassName
     * @param class-string<TClassName> $className
     *
     * @return bool
     */
    public function has(string $className): bool;

    public function call(object $object, string $methodName, mixed ...$params): mixed;

    /**
     * @template T of \Tempest\Container\Initializer
     * @template U of \Tempest\Container\DynamicInitializer
     * @param ReflectionClass|class-string<T>|class-string<U> $initializerClass
     *
     * @return self
     */
    public function addInitializer(ReflectionClass|string $initializerClass): self;
}
