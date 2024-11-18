<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Tempest\Console\Components\State;
use Tempest\Console\Components\TextBuffer;
use Tempest\Console\Point;
use Tempest\Console\Terminal\Terminal;

final class SearchRenderer
{
    use RendersInput;

    public function render(
        Terminal $terminal,
        State $state,
        ?string $label,
        TextBuffer $query,
        array $options,
        null|int|string $selected,
        int $hiddenOptions,
    ): string {
        $this->prepareRender($terminal, $state);
        $this->label($label);
        $this->newLine();
        $this->line(
            $this->style('fg-magenta', '/ '),
            $this->style('bold fg-cyan', $this->truncateLeft($query->text, maxLineOffset: 2))
        );
        $this->newLine();

        foreach ($options as $key => $value) {
            $this->line(
                $key === $selected ? $this->style('fg-magenta', '→ ') : '  ',
                $this->style($key === $selected ? 'fg-green bold' : 'fg-white', $value),
            );
            $this->newLine();
        }

        if ($hiddenOptions > 0) {
            $this->line(
                $this->style('fg-gray', '  '),
                $this->style('fg-gray', sprintf('and %d more...', $hiddenOptions)),
            );
            $this->newLine();
        }

        return $this->finishRender();
    }

    public function getCursorPosition(Terminal $terminal, TextBuffer $buffer): Point
    {
        return new Point(
            x: mb_strlen($this->truncateLeft($buffer->text, maxLineOffset: 2)) + (self::MARGIN_X + 1 + self::PADDING_X) + 2, // +1 is the border width, +2 is for decoration
            y: self::MARGIN_TOP + 1, // +1 because of label
        );
    }
}
