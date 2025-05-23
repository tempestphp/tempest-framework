<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Reflection\ClassReflector;
use UnitEnum;

interface DynamicInitializer
{
    public function canInitialize(ClassReflector $class, null|string|UnitEnum $tag): bool;

    public function initialize(ClassReflector $class, null|string|UnitEnum $tag, Container $container): object;
}
