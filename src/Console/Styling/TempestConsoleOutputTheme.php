<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

use Tempest\Console\ConsoleStyle;

final class TempestConsoleOutputTheme implements ConsoleOutputTheme
{
    public function format(OutputLine $line): string
    {
        $format = $line->type;

        if (! $line->line) {
            return '';
        }

        return match ($format) {
            LineType::Brand => ConsoleStyle::BG_DARK_BLUE(
                ConsoleStyle::FG_WHITE(
                    ConsoleStyle::BOLD(" $line->line ")
                )
            ),
            LineType::Error => ConsoleStyle::BG_RED(ConsoleStyle::FG_WHITE(ConsoleStyle::BOLD($line->line))),
            LineType::Warning => ConsoleStyle::FG_DARK_YELLOW($line->line),
            LineType::Success => ConsoleStyle::BG_GREEN(ConsoleStyle::FG_WHITE($line->line)),
            LineType::Info => ConsoleStyle::FG_BLUE($line->line),
            LineType::Comment => ConsoleStyle::FG_DARK_GREEN(($line->line)),
            LineType::Muted => ConsoleStyle::FG_LIGHT_GRAY(($line->line)),
            default => $line->line,
        };
    }
}
