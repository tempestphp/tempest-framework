<?php

declare(strict_types=1);

namespace Tempest\Container;

use Psr\Container\ContainerInterface;

interface Container extends ContainerInterface
{
    public function register(string $className, callable $definition): self;

    public function singleton(string $className, callable $definition): self;

    public function config(object $config): self;

    /**
     * @template TClassName
     * @param class-string<TClassName> $id
     * @return TClassName
     */
    public function get(string $id): object;

    public function has(string $id): bool;

    public function call(object $object, string $methodName, mixed ...$params): mixed;

    public function addInitializer(CanInitialize $initializer): self;
}
