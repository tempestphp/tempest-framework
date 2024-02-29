<?php

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class CircularC
{
    public function __construct(public CircularA $a) {}
}