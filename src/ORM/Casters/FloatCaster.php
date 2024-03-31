<?php

declare(strict_types=1);

namespace Tempest\ORM\Casters;

use ReflectionProperty;
use ReflectionParameter;
use Tempest\ORM\DynamicCaster;

final readonly class FloatCaster implements DynamicCaster
{
    public function cast(mixed $input): float
    {
        return floatval($input);
    }

    public function shouldCast(ReflectionParameter|ReflectionProperty $property, mixed $value): bool
    {
        return $property->getType()?->getName() === 'float';
    }
}
