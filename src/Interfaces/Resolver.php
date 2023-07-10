<?php

namespace Tempest\Interfaces;

interface Resolver
{
    public function canResolve(string $className): bool;

    public function resolve(string $className, Container $container): object;
}