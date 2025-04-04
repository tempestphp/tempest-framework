<?php

namespace Tempest\Container;

use Attribute;

/**
 * Resolves this dependency using the tag with which the current class has been resolved.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class ForwardTag
{
}
