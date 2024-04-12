<?php

declare(strict_types=1);

namespace Tempest\Console;

interface Cursor
{
    public function getPosition(): Point;

    public function moveUp(int $amount): self;

    public function moveDown(int $amount): self;

    public function moveLeft(int $amount): self;

    public function moveRight(int $amount): self;

    public function place(Point $position): self;

    public function clearLine(): self;

    public function clearAfter(): self;

    public function startOfLine(): self;
}
