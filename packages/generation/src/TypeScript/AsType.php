<?php

namespace Tempest\Generation\TypeScript;

use Attribute;

/**
 * Marks this class as a source for TypeScript type generation.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class AsType
{
}
