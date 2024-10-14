<?php

declare(strict_types=1);

namespace Tempest\Console\Terminal;

use Generator;
use Tempest\Console\Console;
use Tempest\Console\Cursor;
use Tempest\Console\HasCursor;
use Tempest\Console\InteractiveConsoleComponent;
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

    public function switchToInteractiveMode(): self
    {
        $this->tty = exec('stty -g');
        system("stty -echo");
        system("stty -icanon");
        system("stty -isig");

        return $this;
    }

    public function switchToNormalMode(): self
    {
        $this->cursor->show();

        if ($this->tty) {
            system("stty {$this->tty}");
        }

        system("stty echo");
        system("stty icanon");
        system("stty isig");
        $this->tty = null;

        $this->console->writeln();

        return $this;
    }

    public function render(
        InteractiveConsoleComponent $component,
        array $footerLines = []
    ): Generator {
        $rendered = $component->render();

        if (! $rendered instanceof Generator) {
            $rendered = (function (string $content): Generator {
                yield $content;

                return null;
            })($rendered);
        }

        foreach ($rendered as $content) {
            $footerLinesForContent = $footerLines;

            if ($footer = $component->renderFooter()) {
                $footerLinesForContent[] = $footer;
            }

            if ($footerLinesForContent !== []) {
                $content .= PHP_EOL . implode(PHP_EOL, $footerLinesForContent);
            }

            $this
                ->clear()
                ->write($content)
                ->resetInitialCursor();

            if ($component instanceof HasCursor) {
                $this->placeComponentCursor($component);
            } else {
                $this->cursor->hide();
            }

            yield;
        }

        return $rendered->getReturn();
    }

    private function clear(): self
    {
        if ($this->previousRender === null) {
            return $this;
        }

        $this->cursor
            ->place($this->initialCursor->getPosition())
            ->clearAfter();

        return $this;
    }

    private function resetInitialCursor(): void
    {
        $requiredHeight = substr_count($this->previousRender, PHP_EOL);
        $availableHeight = $this->height - $this->initialCursor->getPosition()->y;

        if ($requiredHeight > $availableHeight) {
            $this->initialCursor->setPosition(new Point(
                x: $this->initialCursor->getPosition()->x,
                y: $this->height - $requiredHeight,
            ));
        }
    }

    private function write(string $content): self
    {
        $this->console->write($content);

        $this->previousRender = $content;

        return $this;
    }

    private function placeComponentCursor(HasCursor $component): self
    {
        $initialCursorPosition = $this->initialCursor->getPosition();

        $componentCursorPosition = $component->getCursorPosition();

        $this->cursor->place(new Point(
            x: $initialCursorPosition->x + $componentCursorPosition->x,
            y: $initialCursorPosition->y + $componentCursorPosition->y,
        ));

        $this->cursor->show();

        return $this;
    }
}
