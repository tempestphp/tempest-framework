<?php

declare(strict_types=1);

namespace Tempest\Container;

interface DynamicInitializer
{
    public function canInitialize(string $className): bool;

    public function initialize(string $className, Container $container): object;
}
