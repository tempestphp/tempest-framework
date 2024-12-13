<?php

declare(strict_types=1);

namespace Tempest\Console\Components\Renderers;

use Tempest\Console\Components\ComponentState;
use Tempest\Console\Terminal\Terminal;
use function Tempest\Support\str;

final class SpinnerRenderer
{
    private const array FRAMES = [ '⠁', '⠉', '⠙', '⠚', '⠒', '⠂', '⠂', '⠒', '⠲', '⠴', '⠤', '⠄', '⠄', '⠤', '⠴', '⠲', '⠒', '⠂', '⠂', '⠒', '⠚', '⠙', '⠉', '⠁'];

    private int $index = 0;

    private(set) int $speed = 80_000;

    public function __construct(
        private readonly string $label,
    ) {
    }

    public function render(Terminal $terminal, ComponentState $state): string
    {
        if ($state !== ComponentState::ACTIVE) {
            return '';
        }

        $margin = str_repeat(' ', times: 2);
        $previous = $this->index;

        $this->index = ($this->index + 1) % count(self::FRAMES);

        return self::FRAMES[$previous];

        return str()
            ->append("\n")
            ->append($margin)
            ->append('<style="fg-cyan">', self::FRAMES[$previous], '</style>')
            ->append(' ')
            ->append(str($this->label)->truncate(min(150, $terminal->width - strlen($margin) * 2)))
            ->append("\n")
            ->toString();
    }
}
