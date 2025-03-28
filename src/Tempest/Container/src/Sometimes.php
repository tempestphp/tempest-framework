<?php

declare(strict_types=1);

namespace Tempest\Container;

use Attribute;

/**
 * Add this to an attribute that has #[Inject] or a constructor parameter to indicate
 * that your class might not always use this dependency.
 * The container may then decide to do lazy initialization
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class Sometimes
{
}
