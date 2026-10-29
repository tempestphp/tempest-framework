<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Reflection\ClassReflector;
use UnitEnum;

interface DynamicInitializer
{
    /**
     * Determines whether this initializer can initialize the given class and tag. The outcome is remembered by the container, so it should only depend on the class and tag.
     */
    public function canInitialize(ClassReflector $class, string|UnitEnum|null $tag): bool;

    public function initialize(ClassReflector $class, string|UnitEnum|null $tag, Container $container): object;
}
