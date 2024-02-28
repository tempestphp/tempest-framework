<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

class BuiltinArrayClass
{
    public function __construct(public array $anArray)
    {
    }
}
