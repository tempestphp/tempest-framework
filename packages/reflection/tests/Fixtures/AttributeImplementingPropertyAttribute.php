<?php

namespace Tempest\Reflection\Tests\Fixtures;

use Attribute;
use Tempest\Reflection\PropertyAttribute;
use Tempest\Reflection\PropertyReflector;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class AttributeImplementingPropertyAttribute implements PropertyAttribute
{
    public PropertyReflector $property;
}