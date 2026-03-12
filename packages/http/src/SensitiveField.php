<?php

declare(strict_types=1);

namespace Tempest\Http;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SensitiveField
{
}
