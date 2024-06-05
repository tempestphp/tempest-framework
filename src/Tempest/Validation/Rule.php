<?php

declare(strict_types=1);

namespace Tempest\Validation;

interface Rule
{
    public function isValid(mixed $value): bool;

    /**
     * @return string|string[]
     */
    public function message(): string|array;
}
