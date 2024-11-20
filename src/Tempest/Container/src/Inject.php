<?php

declare(strict_types=1);

namespace Tempest\Container;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Inject
{
}
