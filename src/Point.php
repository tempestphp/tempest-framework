<?php

namespace Tempest\Console;

final class Point
{
    public function __construct(
        public int $x,
        public int $y,
    ) {}

    public function equals(self $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }
}