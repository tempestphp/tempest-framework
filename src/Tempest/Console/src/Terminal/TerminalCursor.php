<?php

declare(strict_types=1);

namespace Tempest\Console\Terminal;

use Tempest\Console\Console;
use Tempest\Console\Cursor;
use Tempest\Console\Point;

final class TerminalCursor implements Cursor
{
    public Point $position;

    public function __construct(
        private readonly Console $console,
        private readonly Terminal $terminal,
    ) {
        $this->position = $this->getActualPosition();
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function setPosition(Point $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function moveUp(int $amount = 1): self
    {
        $this->place(new Point(
            x: $this->position->x,
            y: $this->position->y - $amount,
        ));

        return $this;
    }

    public function moveDown(int $amount = 1): self
    {
        $this->place(new Point(
            x: $this->position->x,
            y: $this->position->y + $amount,
        ));

        return $this;
    }

    public function moveLeft(int $amount = 1): self
    {
        $this->place(new Point(
            x: $this->position->x - $amount,
            y: $this->position->y,
        ));

        return $this;
    }

    public function moveRight(int $amount = 1): self
    {
        $this->place(new Point(
            x: $this->position->x + $amount,
            y: $this->position->y,
        ));

        return $this;
    }

    public function place(Point $position): self
    {
        $position = new Point(
            x: min($this->terminal->width, max(1, $position->x)),
            y: min($this->terminal->height, max(1, $position->y)),
        );

        $this->console->write(
            sprintf(
                "\e[%d;%dH",
                $position->y,
                $position->x,
            ),
        );

        $this->position = $position;

        return $this;
    }

    public function clearLine(): self
    {
        $this->console->write("\e[2K");

        return $this;
    }

    public function clearAfter(): self
    {
        $this->console->write("\e[0J");

        return $this;
    }

    public function startOfLine(): self
    {
        $this->console->writeln("\e[1G");

        return $this;
    }

    public function hide(): self
    {
        $this->console->write("\e[?25l");

        return $this;
    }

    public function show(): self
    {
        $this->console->write("\e[?25h");

        return $this;
    }

    public function placeToEnd(): self
    {
        $this->place(new Point(
            x: $this->terminal->width,
            y: $this->terminal->height,
        ));

        return $this;
    }

    public function getActualPosition(): Point
    {
        $this->console->write("\e[6n");

        preg_match('/(?<y>[\d]+);(?<x>[\d]+)/', $this->console->read(1024), $matches);

        return new Point((int) ($matches['x'] ?? 100), (int) ($matches['y'] ?? 25));
    }
}
