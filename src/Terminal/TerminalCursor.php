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
        private readonly InteractiveTerminal $terminal,
    ) {
        $this->console->write("\033[6n");

        preg_match('/(?<y>[\d]+);(?<x>[\d]+)/', fread(STDIN, 1024), $matches);

        $this->position = new Point((int)($matches['x'] ?? 1), (int)($matches['y'] ?? 1));
    }

    public function getPosition(): Point
    {
        return $this->position;
    }

    public function moveUp(int $amount): self
    {
        $this->place(new Point(
            x: $this->position->x,
            y: $this->position->y - $amount,
        ));

        return $this;
    }

    public function moveDown(int $amount): self
    {
        $this->place(new Point(
            x: $this->position->x,
            y: $this->position->y + $amount,
        ));

        return $this;
    }

    public function moveLeft(int $amount): self
    {
        $this->place(new Point(
            x: $this->position->x - $amount,
            y: $this->position->y,
        ));

        return $this;
    }

    public function moveRight(int $amount): self
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
                "\x1b[%d;%dH",
                $position->y,
                $position->x,
            ),
        );

        $this->position = $position;

        return $this;
    }

    public function clearLine(): self
    {
        $this->console->write("\x1b[2K");

        return $this;
    }

    public function clearAfter(): self
    {
        $this->console->write("\x1b[0J");

        return $this;
    }

    public function startOfLine(): self
    {
        $this->console->writeln("\r");

        return $this;
    }
}
