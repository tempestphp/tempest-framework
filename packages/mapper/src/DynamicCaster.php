<?php

namespace Tempest\Mapper;

use Tempest\Reflection\PropertyReflector;

interface DynamicCaster
{
    public static function make(PropertyReflector $property): Caster;
}
