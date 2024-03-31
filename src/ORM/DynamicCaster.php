<?php

declare(strict_types=1);

namespace Tempest\ORM;

use ReflectionParameter;
use ReflectionProperty;

interface DynamicCaster extends Caster
{
    public function shouldCast(ReflectionParameter|ReflectionProperty $property, mixed $value): bool;
}
