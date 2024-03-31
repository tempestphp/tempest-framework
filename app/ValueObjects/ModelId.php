<?php

declare(strict_types=1);

namespace App\ValueObjects;

use ReflectionParameter;
use ReflectionProperty;
use Tempest\ORM\DynamicCaster;
use Tempest\Validation\Inferrer;
use Tempest\Validation\Rules\StartsWith;

final class ModelId implements DynamicCaster, Inferrer
{
    public function __construct(
        public int $value,
    ) {

    }

    public function cast(mixed $input): mixed
    {
        return new self($input);
    }

    public function shouldCast(ReflectionParameter|ReflectionProperty $property, mixed $value): bool
    {
        if ($property->getType()?->getName() === self::class) {
            return true;
        }

        return false;
    }

    public function infer(ReflectionParameter|ReflectionProperty $reflection, mixed $value): array
    {
        if ($reflection->getType()?->getName() === self::class) {
            return [new StartsWith("5")];
        }

        return [];
    }
}
