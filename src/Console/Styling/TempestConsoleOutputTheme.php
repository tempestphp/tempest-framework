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
            OutputType::Brand => ConsoleStyle::BG_DARK_BLUE(
                ConsoleStyle::FG_WHITE(
                    ConsoleStyle::BOLD(" $line->line ")
                )
            ),
            OutputType::Error => ConsoleStyle::BG_RED(ConsoleStyle::FG_WHITE(ConsoleStyle::BOLD($line->line))),
            OutputType::Warning => ConsoleStyle::FG_DARK_YELLOW($line->line),
            OutputType::Success => ConsoleStyle::BG_GREEN(ConsoleStyle::FG_WHITE($line->line)),
            OutputType::Info => ConsoleStyle::FG_BLUE($line->line),
            OutputType::Comment => ConsoleStyle::FG_DARK_GREEN(($line->line)),
            OutputType::Muted => ConsoleStyle::FG_LIGHT_GRAY(($line->line)),
            default => $line->line,
        };
    }
}
