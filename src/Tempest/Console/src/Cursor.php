<?php

declare(strict_types=1);

namespace Tempest\Console;

interface Cursor
{
    public function getPosition(): Point;

    public function setPosition(Point $position): self;

    public function moveUp(int $amount = 1): self;

    public function moveDown(int $amount = 1): self;

    public function moveLeft(int $amount = 1): self;

    public function moveRight(int $amount = 1): self;

    public function place(Point $position): self;

    public function clearLine(): self;

    public function clearAfter(): self;

    public function startOfLine(): self;

    public function hide(): self;

    public function show(): self;
}
