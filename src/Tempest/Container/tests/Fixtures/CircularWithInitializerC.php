<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class CircularWithInitializerC
{
    public function __construct()
    {
    }
}
