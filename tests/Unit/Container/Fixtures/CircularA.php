<?php

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class CircularA
{
    public function __construct(public CircularB $b) {}
}