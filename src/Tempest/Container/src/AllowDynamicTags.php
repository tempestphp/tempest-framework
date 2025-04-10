<?php

namespace Tempest\Container;

use Attribute;

/**
 * Allows this class to be instanciated with any tag.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AllowDynamicTags
{
}
