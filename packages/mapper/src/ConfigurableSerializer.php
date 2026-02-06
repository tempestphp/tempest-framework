<?php

namespace Tempest\Mapper;

use Tempest\Reflection\PropertyReflector;
use Tempest\Reflection\TypeReflector;

/**
 * A serializer that may be configured by the property or type it is applied to.
 */
interface ConfigurableSerializer
{
    /**
     * Creates a serializer configured for the given property or type and context.
     */
    public static function configure(PropertyReflector|TypeReflector|string $input, Context $context): Serializer;
}
