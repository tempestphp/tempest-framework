<?php

namespace Tempest\Interfaces;

interface Container
{
    public function register(string $className, callable $definition): self;

    public function singleton(string $className, callable $definition): self;

    public function config(object $config): self;

    /**
     * @template TClassName
     * @param class-string<TClassName> $className
     * @return TClassName
     */
    public function get(string $className): object;

    public function call(object $object, string $methodName, mixed ...$params): mixed;

    public function addResolver(Resolver $resolver): self;
}