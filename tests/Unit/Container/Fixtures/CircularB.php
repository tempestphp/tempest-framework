<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class CircularB
{
    public function __construct(public CircularC $c)
    {
    }
}
