<?php

declare(strict_types=1);

namespace Tempest\Interface;

interface Rule
{
    public function isValid(mixed $value): bool;

    public function message(): string;
}
