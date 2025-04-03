<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Reflection\ClassReflector;

interface DynamicInitializer
{
    public function canInitialize(ClassReflector $class): bool;

    public function initialize(ClassReflector $class, Container $container): object;
}
