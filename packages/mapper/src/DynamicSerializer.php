<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

interface DynamicSerializer
{
    /**
     * Determines whether this serializer can handle the given property or type.
     */
    public static function accepts(PropertyReflector|TypeReflector $input): bool;
}
