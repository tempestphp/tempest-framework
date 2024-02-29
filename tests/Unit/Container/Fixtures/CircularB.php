<?php

namespace Tests\Tempest\Unit\Container\Fixtures;

final readonly class CircularB
{
    public function __construct(public CircularC $c) {}
}