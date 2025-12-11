<?php

namespace Tempest\Mapper;

use Tempest\Reflection\PropertyReflector;

/**
 * A serializer that may be configured by the property it is applied to.
 */
interface ConfigurableCaster
{
    /**
     * Creates a caster configured for the given property and context.
     */
    public static function configure(PropertyReflector $property, Context $context): Caster;
}
