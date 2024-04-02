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

        if ($format === LineType::Brand) {
            return ConsoleStyle::BG_DARK_BLUE(
                ConsoleStyle::FG_WHITE(
                    ConsoleStyle::BOLD(" $line->line ")
                )
            );
        }

        if ($format === LineType::Error) {
            return ConsoleStyle::BG_RED(ConsoleStyle::FG_WHITE($line->line));
        }

        if ($format === LineType::Warning) {
            return ConsoleStyle::BG_YELLOW(ConsoleStyle::FG_WHITE($line->line));
        }

        if ($format === LineType::Success) {
            return ConsoleStyle::BG_GREEN(ConsoleStyle::FG_WHITE($line->line));
        }

        if ($format === LineType::Info) {
            return ConsoleStyle::FG_BLUE($line->line);
        }

        if ($format === LineType::Comment) {
            return ConsoleStyle::FG_DARK_GREEN(($line->line));
        }

        if ($format === LineType::Muted) {
            return ConsoleStyle::FG_LIGHT_GRAY(($line->line));
        }

        return ConsoleStyle::FG_DARK_GREEN($line->line);
    }
}
