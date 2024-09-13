<?php

declare(strict_types=1);

namespace Tempest\Container\Tests\Fixtures;

final readonly class CircularB
{
    public function __construct(public CircularC $c)
    {
    }
}
