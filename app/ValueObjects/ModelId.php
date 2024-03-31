<?php

declare(strict_types=1);

namespace App\ValueObjects;

use ReflectionProperty;
use ReflectionParameter;
use Tempest\ORM\DynamicCaster;
use Tempest\Validation\Inferrer;
use Tempest\Validation\Rules\StartsWith;

final class ModelId implements DynamicCaster
{

    public function __construct(
        public int $value,
    )
    {

    }

    public function cast(mixed $input): mixed
    {
        return $input;
    }

    public function shouldCast(ReflectionParameter|ReflectionProperty $property, mixed $value): bool
    {
        return false;
    }
}
