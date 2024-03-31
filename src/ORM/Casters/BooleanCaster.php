<?php

declare(strict_types=1);

namespace Tempest\ORM\Casters;

use ReflectionParameter;
use ReflectionProperty;
use Tempest\Mapper\UnknownValue;
use Tempest\ORM\DynamicCaster;
use Tempest\Validation\Inferrer;
use Tempest\Validation\Rules\Boolean;

final readonly class BooleanCaster implements DynamicCaster, Inferrer
{
    public function cast(mixed $input): bool
    {
        return boolval($input);
    }

    public function shouldCast(ReflectionParameter|ReflectionProperty $property, mixed $value): bool
    {
        if ($property->getType()?->getName() !== 'bool') {
            return false;
        }

        foreach ($this->infer($property, $value) as $rule) {
            if (! $rule->isValid($value)) {
                return false;
            }
        }

        return true;
    }

    public function infer(ReflectionProperty|ReflectionParameter $reflection, mixed $value): array
    {
        if ($value instanceof UnknownValue) {
            return [];
        }

        if ($reflection->getType()?->getName() === 'bool') {
            return [new Boolean()];
        }

        return [];
    }
}
