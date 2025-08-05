<?php

declare(strict_types=1);

namespace Tempest\Validation;

interface Rule
{
    /**
     * Determines whether the given value is valid for this rule.
     */
    public function isValid(mixed $value): bool;
}
