<?php

declare(strict_types=1);

namespace Tempest\Database;

use Attribute;

/**
 * Virtual properties are ignored by the database mapper.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Virtual
{
}
