<?php

namespace Tempest\Mapper;

use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

interface DynamicSerializer
{
    public static function make(PropertyReflector|TypeReflector|string $input): Serializer;
}
