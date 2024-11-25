<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Tempest\Console\Components\State;
use Tempest\Console\Components\TextBuffer;
use Tempest\Console\Point;
use Tempest\Console\Terminal\Terminal;
use function Tempest\Support\str;

final class TextInputRenderer
{
    use RendersInput;

    private int $scrollOffset = 0;

    public function __construct(
        private ?bool $multiline = false,
        private ?int $minimumLines = null,
        private int $maximumLines = 5,
    ) {
    }

    public function render(
        Terminal $terminal,
        State $state,
        TextBuffer $buffer,
        ?string $label,
        ?string $placeholder = null,
        ?string $hint = null,
    ): string {
        $this->prepareRender($terminal, $state);

        $this->label($label);

        if ($hint) {
            $this->offsetY++;
            $this->line($this->style('fg-gray', $hint))->newLine();
        }

        // splits the text to an array so we can work with individual lines
        $lines = str(($buffer->text ?: $placeholder) ?: '')
            ->explode(PHP_EOL)
            ->flatMap(fn (string $line) => str($line)->split($this->maxLineCharacters)->toArray())
            ->map(static fn (string $line) => str($line)->replaceEnd(PHP_EOL, ' '));

        // calculates scroll offset based on cursor position
        $this->scrollOffset = $this->calculateScrollOffset($lines, $this->maximumLines, $buffer->getRelativeCursorPosition($this->maxLineCharacters)->y);

        // slices lines to display only the visible portion
        $displayLines = $lines->slice($this->scrollOffset, $this->maximumLines);

        // renders visible lines
        foreach ($displayLines as $line) {
            $this->line($this->style(empty($buffer->text) ? 'fg-gray' : null, $line))->newLine();
        }

        // fills remaining lines if less than max display lines
        if ($this->multiline && $state !== State::CANCELLED) {
            for ($i = $displayLines->count(); $i < $this->maximumLines; $i++) {
                $this->line(PHP_EOL);
            }
        } elseif ($displayLines->count() === 0) {
            $this->line(PHP_EOL);
        }

        return $this->finishRender();
    }

    public function getCursorPosition(Terminal $terminal, TextBuffer $buffer): Point
    {
        $position = $buffer->getRelativeCursorPosition($terminal->width - self::MARGIN_X - 1 - self::PADDING_X - self::MARGIN_X);

        return new Point(
            x: $position->x + (self::MARGIN_X + 1 + self::PADDING_X), // +1 is the border width
            y: $position->y - $this->scrollOffset + (self::MARGIN_TOP + $this->offsetY), // subtract scroll offset
        );
    }
}
