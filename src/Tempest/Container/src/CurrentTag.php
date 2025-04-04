<?php

namespace Tempest\Container;

use Attribute;

/**
 * Injects to the property the tag with which the current class has been resolved.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class CurrentTag
{
}
