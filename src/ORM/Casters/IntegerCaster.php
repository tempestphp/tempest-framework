<?php

declare(strict_types=1);

namespace Tempest\ORM\Casters;

use ReflectionParameter;
use ReflectionProperty;
use Tempest\ORM\DynamicCaster;

final readonly class IntegerCaster implements DynamicCaster
{
    public function cast(mixed $input): int
    {
        return intval($input);
    }

    public function shouldCast(ReflectionParameter|ReflectionProperty $property, mixed $value): bool
    {
        return $property->getType()?->getName() === 'int';
    }
}
