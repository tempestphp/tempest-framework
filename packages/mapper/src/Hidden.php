<?php

declare(strict_types=1);

namespace Tempest\Mapper;

use Attribute;

/**
 * Hidden properties are excluded from SELECT queries and serialization.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Hidden
{
}
