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

    public function __construct(
        private ?bool $multiline = false,
        private ?int $minimumLines = null,
    ) {
    }

    public function render(
        Terminal $terminal,
        State $state,
        TextBuffer $buffer,
        ?string $label,
        ?string $placeholder = null,
    ): string {
        $this->prepareRender($terminal, $state);

        $this->label($label);
        $this->newLine();

        // splits the text to an array so we can work with individual lines
        $lines = str($buffer->text ?: $placeholder ?: '')
            ->explode(PHP_EOL)
            ->flatMap(fn (string $line) => str($line)->split($this->maxLineCharacters)->toArray())
            ->map(static fn (string $line) => str($line)->replaceEnd(PHP_EOL, ' '));

        // If we support multiple lines, but the current amount of lines is below
        // the max we suppport, we add them, except if user has cancelled input.
        if ($this->multiline && $state !== State::CANCELLED) {
            $this->minimumLines ??= $lines->count();

            if ($lines->count() < $this->minimumLines) {
                $lines = $lines->merge(array_fill(0, count: $this->minimumLines - $lines->count(), value: str()));
            }
        }

        // If there is no line or if the last line has the max amount of characters
        // we need to display a new line below.
        if ($lines->count() === 0 || $lines->last()?->length() === $this->maxLineCharacters) {
            $lines->push(str());
        }

        // otherwise, print each line
        foreach ($lines as $line) {
            $this->line($this->style(empty($buffer->text) ? 'fg-gray' : null, $line))->newLine();
        }

        return $this->finishRender();
    }

    public function getCursorPosition(Terminal $terminal, TextBuffer $buffer): Point
    {
        $position = $buffer->getRelativeCursorPosition($terminal->width - self::MARGIN_X - 1 - self::PADDING_X - self::MARGIN_X);

        return new Point(
            x: $position->x + (self::MARGIN_X + 1 + self::PADDING_X), // +1 is the border width
            y: $position->y + (self::MARGIN_TOP + 1), // +1 is the label
        );
    }
}
