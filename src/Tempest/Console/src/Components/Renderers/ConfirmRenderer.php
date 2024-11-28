<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Tempest\Console\Components\ComponentState;
use Tempest\Console\Components\TextBuffer;
use Tempest\Console\Point;
use Tempest\Console\Terminal\Terminal;

final class ConfirmRenderer
{
    use RendersInput;

    public function __construct(
        private string $yes,
        private string $no,
    ) {
    }

    public function render(
        Terminal $terminal,
        ComponentState $state,
        bool $answer,
        ?string $label,
    ): string {
        $this->prepareRender($terminal, $state);
        $this->label($label);
        $this->newLine(border: true);

        match ($this->state) {
            ComponentState::SUBMITTED => $this->line(
                $this->style($answer === true ? 'bg-green bold' : 'bg-red bold', $this->centerText($answer ? $this->yes : $this->no, width: 9)),
                "\n"
            ),
            default => $this->line(
                $this->style($answer === true ? 'bg-green bold' : 'bg-gray dim', $this->centerText($this->yes, width: 9)),
                ' ',
                $this->style($answer === false ? 'bg-red bold' : 'bg-gray dim', $this->centerText($this->no, width: 9)),
                "\n"
            ),
        };

        return $this->finishRender();
    }

    public function getCursorPosition(Terminal $terminal, TextBuffer $buffer): Point
    {
        $position = $buffer->getRelativeCursorPosition($terminal->width - self::MARGIN_X - 1 - self::PADDING_X - self::MARGIN_X);

        return new Point(
            x: $position->x + (self::MARGIN_X + 1 + self::PADDING_X), // +1 is the border width
            y: $position->y + (self::MARGIN_TOP + $this->offsetY),
        );
    }
}
