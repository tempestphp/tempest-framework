<?php

declare(strict_types=1);

namespace Tempest\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class SkipValidation
{
}
