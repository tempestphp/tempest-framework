<?php

declare(strict_types=1);

namespace Tempest\Console;

final class ConsoleOutputLine
{
    public function __construct(
        public string $line,
        public ConsoleOutputType $type,
    ) {
    }

    public function format(): string
    {
        if (! $this->line) {
            return '';
        }

        return match ($this->type) {
            ConsoleOutputType::Brand => ConsoleStyle::BG_DARK_BLUE(
                ConsoleStyle::FG_WHITE(
                    ConsoleStyle::BOLD(" $this->line ")
                )
            ),
            ConsoleOutputType::Error => ConsoleStyle::BG_RED(ConsoleStyle::FG_WHITE(ConsoleStyle::BOLD($this->line))),
            ConsoleOutputType::Warning => ConsoleStyle::FG_DARK_YELLOW($this->line),
            ConsoleOutputType::Success => ConsoleStyle::BG_GREEN(ConsoleStyle::FG_WHITE($this->line)),
            ConsoleOutputType::Info => ConsoleStyle::FG_BLUE($this->line),
            ConsoleOutputType::Comment => ConsoleStyle::FG_DARK_GREEN(($this->line)),
            ConsoleOutputType::Muted => ConsoleStyle::FG_LIGHT_GRAY(($this->line)),
            default => $this->line,
        };
    }
}
