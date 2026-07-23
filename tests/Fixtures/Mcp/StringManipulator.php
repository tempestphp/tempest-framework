<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Mcp;

final readonly class StringManipulator
{
    public function upper(string $value): string
    {
        return strtoupper($value);
    }
}
