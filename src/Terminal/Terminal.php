<?php

declare(strict_types=1);

namespace Tempest\Console\Terminal;

use Generator;
use Tempest\Console\Console;
use Tempest\Console\ConsoleComponent;
use Tempest\Console\Cursor;
use Tempest\Console\HasCursor;
use Tempest\Console\HasFooter;
use Tempest\Console\Point;

final class Terminal
{
    public int $width;
    public int $height;
    public Cursor $cursor;
    private Cursor $initialCursor;
    private ?string $previousRender = null;
    private ?string $tty = null;

    public function __construct(
        private readonly Console $console,
    ) {
        $this->switchToInteractiveMode();
        $this->width = (int)exec('tput cols');
        $this->height = (int)exec('tput lines');
        $this->initialCursor = new TerminalCursor($this->console, $this);
        $this->cursor = clone $this->initialCursor;
    }

    public function render(
        ConsoleComponent $component,
        array $footerLines = [],
        bool $renderFooter = true,
    ): mixed {
        $rendered = $component->render();

        if ($renderFooter) {
            if ($component instanceof HasFooter) {
                $footerLines = [...$footerLines, $component->renderFooter()];
            }

            if ($footerLines !== []) {
                $rendered .= PHP_EOL . PHP_EOL . implode(PHP_EOL, $footerLines);
            }
        }

        if (is_string($rendered)) {
            $rendered = [$rendered];
        }

        foreach ($rendered as $content) {
            $this->clear();
            $this->console->write($content);
            $this->previousRender = $content;

            $initialCursorPosition = $this->initialCursor->getPosition();
            if ($component instanceof HasCursor) {
                $this->cursor->show();

                $componentCursorPosition = $component->getCursorPosition();

                $this->cursor->place(new Point(
                    x: $initialCursorPosition->x + $componentCursorPosition->x,
                    y: $initialCursorPosition->y + $componentCursorPosition->y,
                ));
            } else {
                $this->cursor->hide();
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

        $this->cursor->place($this->initialCursor->getPosition());
        $this->cursor->clearAfter();
    }

    public function switchToInteractiveMode(): self
    {
        $this->tty = exec('stty -g');
        system("stty -echo");
        system("stty -icanon");

        return $this;
    }

    public function switchToNormalMode(): self
    {
        $this->cursor->show();
        system("stty {$this->tty}");
        system("stty echo");
        system("stty icanon");
        $this->tty = null;

        $this->console->writeln();

        return $this;
    }
}
