<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

interface DynamicCaster
{
    /**
     * Determines whether this caster can handle the given property or type.
     */
    public static function accepts(PropertyReflector|TypeReflector $input): bool;
}
