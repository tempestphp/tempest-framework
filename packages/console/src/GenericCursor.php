<?php

declare(strict_types=1);

namespace Tempest\Console;

final class GenericCursor implements Cursor
{
    public Point $position;

    public function __construct()
    {
        $this->position = new Point(0, 0);
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function setPosition(Point $position): Cursor
    {
        $this->position = $position;

        return $this;
    }

    public function moveUp(int $amount = 1): Cursor
    {
        $this->position->y -= $amount;

        return $this;
    }

    public function moveDown(int $amount = 1): Cursor
    {
        $this->position->y += $amount;

        return $this;
    }

    public function moveLeft(int $amount = 1): Cursor
    {
        $this->position->x -= $amount;

        return $this;
    }

    public function moveRight(int $amount = 1): Cursor
    {
        $this->position->x += $amount;

        return $this;
    }

    public function place(Point $position): Cursor
    {
        return $this->setPosition($position);
    }

    public function placeToEnd(): Cursor
    {
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

    public function hide(): Cursor
    {
        return $this;
    }

    public function show(): Cursor
    {
        return $this;
    }
}
