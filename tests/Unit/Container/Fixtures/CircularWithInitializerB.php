<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class CircularWithInitializerB
{
    /** @phpstan-ignore-next-line */
    public function __construct()
    {
    }
}
