<?php

declare(strict_types=1);

namespace App;

final readonly class TestDependency
{
    public function __construct(public string $input)
    {
    }
}
