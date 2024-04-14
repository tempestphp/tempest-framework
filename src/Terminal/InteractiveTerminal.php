<?php

declare(strict_types=1);

namespace Tempest\Console\Terminal;

use Generator;
use Tempest\Console\Console;
use Tempest\Console\ConsoleComponent;
use Tempest\Console\HasCursor;
use Tempest\Console\Point;

final class InteractiveTerminal
{
    public int $width;

    public int $height;

    public TerminalCursor $cursor;

    private ?string $previousRender = null;

    private ?Point $currentCursorPosition = null;

    public function __construct(
        private readonly Console $console,
    ) {
        $this->switchToInteractiveMode();
        $this->width = (int)exec('tput cols');
        $this->height = (int)exec('tput lines');
        $this->cursor = new TerminalCursor($this->console, $this);
    }

    public function render(ConsoleComponent $component): mixed
    {
        $rendered = $component->render();

        if (is_string($rendered)) {
            $rendered = [$rendered];
        }

        foreach ($rendered as $content) {
            $this->restoreCurrentCursorPosition();

            if ($this->previousRender) {
                $this->clear();
            }

            $this->console->write($content);
            $this->previousRender = $content;
            $this->cursor->moveDown(substr_count($content, PHP_EOL));

            if ($component instanceof HasCursor) {
                $this->storeCurrentCursorPosition();
                $component->placeCursor($this->cursor);
            }
        }

        if ($rendered instanceof Generator) {
            return $rendered->getReturn();
        }

        return null;
    }

    private function clear(): void
    {
        if ($this->previousRender === null) {
            return;
        }

        $this->cursor->hide();

        for ($i = 0; $i < substr_count($this->previousRender, PHP_EOL); $i++) {
            $this->cursor->moveUp()->clearLine();
        }

        $this->cursor->show();
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
