<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Tempest\Console\Components\OptionCollection;
use Tempest\Console\Components\State;
use Tempest\Console\Components\TextBuffer;
use Tempest\Console\Point;
use Tempest\Console\Terminal\Terminal;

final class ChoiceRenderer
{
    use RendersInput;

    public function __construct(
        private bool $multiple = true,
        private int $maximumOptions = 10,
        private ?string $default = null,
    ) {
    }

    public function render(
        Terminal $terminal,
        State $state,
        ?string $label,
        TextBuffer $query,
        OptionCollection $options,
        ?string $placeholder = null,
    ): string {
        $this->prepareRender($terminal, $state);
        $this->label($label);

        if ($state === State::SUBMITTED) {
            $this->line($options->getActive()->value)->newLine();
        } elseif ($state === State::CANCELLED) {
            $this->line($this->style('italic', $this->default ?? ''))->newLine();
        } else {
            $this->line(
                $this->style('fg-magenta', '/ '),
                empty($query->text)
                    ? $this->style('fg-gray dim', $placeholder ?? '')
                    : $this->style('bold fg-cyan', $this->truncateLeft($query->text, maxLineOffset: 2))
            );
            $this->newLine();

            if ($state === State::ACTIVE) {
                $displayOptions = $options->getScrollableSection(
                    offset: $this->calculateScrollOffset($options, $this->maximumOptions, $options->getCurrentIndex()),
                    count: $this->maximumOptions,
                );

                foreach ($displayOptions as $option) {
                    $this->line(
                        $options->isActive($option) ? $this->style('fg-magenta', '→ ') : '  ',
                        $this->style($options->isSelected($option) ? 'fg-green bold' : 'fg-white', $option->value),
                    );
                    $this->newLine();
                }
            }
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
