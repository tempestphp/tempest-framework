<?php

declare(strict_types=1);

namespace Tempest\Core;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class DoNotDiscover
{
}
