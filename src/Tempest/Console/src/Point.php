<?php

declare(strict_types=1);

namespace Tempest\Console;

use Stringable;

final class Point implements Stringable
{
    public function __construct(
        public int $x,
        public int $y,
    ) {
    }

    public function equals(self $other): bool
    {
        return $this->x === $other->x && $this->y === $other->y;
    }

    public function __toString(): string
    {
        return "[{$this->x},{$this->y}]";
    }
}
