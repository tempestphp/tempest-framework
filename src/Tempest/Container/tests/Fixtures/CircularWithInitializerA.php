<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class CircularWithInitializerA
{
    /** @phpstan-ignore-next-line */
    public function __construct()
    {
    }
}
