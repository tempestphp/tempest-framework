<?php

namespace Tempest\Console\Terminal;

use Tempest\Console\Console;
use Tempest\Console\ConsoleComponent;
use Tempest\Console\HasCursor;
use Tempest\Console\Point;

final class InteractiveTerminal
{
    public int $width;

    public int $height;

    public Cursor $cursor;

    private ?string $previousRender = null;

    private ?Point $currentCursorPosition = null;

    public function __construct(
        private readonly Console $console,
    ) {
        $this->switchToInteractiveMode();
        $this->width = (int)exec('tput cols');
        $this->height = (int)exec('tput lines');
        $this->cursor = new Cursor($this->console, $this);
    }

    public function render(ConsoleComponent $component): self
    {
        $this->restoreCurrentCursorPosition();

        if ($this->previousRender) {
            $this->clear();
        }

        $contents = $component->render();

        $this->console->write($contents);
        $this->previousRender = $contents;
        $this->cursor->moveDown(substr_count($contents, PHP_EOL));

        if ($component instanceof HasCursor) {
            $this->storeCurrentCursorPosition();
            $component->placeCursor($this->cursor);
        }

        return $this;
    }

    private function clear(): void
    {
        if ($this->previousRender === null) {
            return;
        }

        for ($i = 0; $i < substr_count($this->previousRender, PHP_EOL); $i++) {
            $this->cursor->clearLine()->moveUp(1);
        }
    }

    public function switchToInteractiveMode(): self
    {
        system("stty -echo");
        system("stty -icanon");

        return $this;
    }

    public function switchToNormalMode(): self
    {
        system("stty echo");
        system("stty icanon");

        $this->console->writeln();

        return $this;
    }

    private function storeCurrentCursorPosition(): self
    {
        $this->currentCursorPosition = clone $this->cursor->position;

        return $this;
    }

    private function restoreCurrentCursorPosition(): self
    {
        if ($this->currentCursorPosition === null) {
            return $this;
        }

        $this->cursor->place($this->currentCursorPosition);

        return $this;
    }
}