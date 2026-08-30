<?php

declare(strict_types=1);

namespace Tempest\Container;

use Psr\Container\ContainerInterface;
use Tempest\Reflection\ClassReflector;
use Tempest\Reflection\FunctionReflector;
use Tempest\Reflection\MethodReflector;
use UnitEnum;

interface Container extends ContainerInterface
{
    public function register(string $className, callable $definition): self;

    public function unregister(string $className, bool $tagged = false): self;

    public function singleton(string $className, mixed $definition, string|UnitEnum|null $tag = null): self;

    public function config(object $config): self;

    /**
     * @typephp-ignore
     * @template TClassName of object
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    public function get(string $className, string|UnitEnum|null $tag = null, mixed ...$params): mixed;

    public function has(string $className, string|UnitEnum|null $tag = null): bool;

    public function invoke(ClassReflector|MethodReflector|FunctionReflector|callable|string $method, mixed ...$params): mixed;

    /**
     * @template T of \Tempest\Container\Initializer
     * @template U of \Tempest\Container\DynamicInitializer
     * @param ClassReflector<T>|class-string<T>|class-string<U> $initializerClass
     */
    public function addInitializer(ClassReflector|string $initializerClass): self;

    public function addDecorator(ClassReflector|string $decoratorClass, ClassReflector|string $decoratedClass): self;

    /**
     * @template T of \Tempest\Container\Resettable
     * @param ClassReflector<T>|class-string<T> $resettableClass
     */
    public function addResettable(ClassReflector|string $resettableClass): self;

    public function reset(): self;
}
