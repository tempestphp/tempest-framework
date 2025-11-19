<?php

namespace Tempest\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class TranslationKey
{
    public function __construct(
        private(set) string $key,
    ) {}
}
