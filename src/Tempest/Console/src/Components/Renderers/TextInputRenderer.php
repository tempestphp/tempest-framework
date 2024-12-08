<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Tempest\Console\Components\ComponentState;
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
        private int $maximumLines = 5,
    ) {
    }

    public function render(
        Terminal $terminal,
        ComponentState $state,
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
            ->explode("\n")
            ->flatMap(fn (string $line) => str($line)->split($this->maxLineCharacters)->toArray())
            ->map(static fn (string $line) => str($line)->replaceEnd("\n", ' '));

        // calculates scroll offset based on cursor position
        $this->scrollOffset = $this->calculateScrollOffset($lines, $this->maximumLines, $buffer->getRelativeCursorPosition($this->maxLineCharacters)->y);

        // slices lines to display only the visible portion
        $displayLines = $lines->slice($this->scrollOffset, $this->maximumLines);

        // renders visible lines
        foreach ($displayLines as $line) {
            $this->line($this->style(
                style: match (true) {
                    $this->state === ComponentState::CANCELLED => 'italic fg-gray strikethrough',
                    empty($buffer->text) => 'fg-gray',
                    default => null,
                },
                content: $line,
            ))->newLine();
        }

        // fills remaining lines if less than max display lines
        if ($state !== ComponentState::CANCELLED) {
            $lines = $this->multiline ? $this->maximumLines : 1;

            for ($i = $displayLines->count(); $i < $lines; $i++) {
                $this->line("\n");
            }
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
