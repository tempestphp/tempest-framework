<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final class BuiltinArrayClass
{
    public function __construct(public array $anArray)
    {
    }
}
