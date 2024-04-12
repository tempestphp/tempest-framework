<?php

declare(strict_types=1);

namespace Tempest\Console\Testing;

use Tempest\Console\Cursor;
use Tempest\Console\Point;

final class TestCursor implements Cursor
{
    public function __construct(
        private int $x = 1,
        private int $y = 1,
    ) {
    }

    public function getPosition(): Point
    {
        return new Point($this->x, $this->y);
    }

    public function moveUp(int $amount): Cursor
    {
        $this->y = max(1, $this->y - 1);

        return $this;
    }

    public function moveDown(int $amount): Cursor
    {
        $this->y += 1;

        return $this;
    }

    public function moveLeft(int $amount): Cursor
    {
        $this->x = max(1, $this->x - 1);

        return $this;
    }

    public function moveRight(int $amount): Cursor
    {
        $this->x += 1;

        return $this;
    }

    public function place(Point $position): Cursor
    {
        $this->x = max(1, $position->x);
        $this->y = max(1, $position->y);

        return $this;
    }

    public function clearLine(): Cursor
    {
        return $this;
    }

    public function clearAfter(): Cursor
    {
        return $this;
    }

    public function startOfLine(): Cursor
    {
        return $this;
    }
}
