<?php

namespace Tempest\Container;

use Attribute;

/**
 * Injects the current tag to the target property.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class CurrentTag
{
}
