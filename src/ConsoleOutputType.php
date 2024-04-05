<?php

declare(strict_types=1);

namespace Tempest\Console;

enum ConsoleOutputType
{
    case DEFAULT;
    case INFO;
    case ERROR;
    case SUCCESS;
    case COMMENT;
    case H1;
    case H2;

    public function style(string $input): string
    {
        return match ($this) {
            self::INFO => ConsoleStyle::FG_DARK_BLUE($input),
            self::ERROR => ConsoleStyle::BOLD(ConsoleStyle::FG_WHITE(ConsoleStyle::BG_RED(ConsoleStyle::FG_WHITE($this->error($input))))),
            self::SUCCESS => ConsoleStyle::BOLD(ConsoleStyle::FG_DARK_GREEN($input)),
            self::H1 => ConsoleStyle::BOLD(ConsoleStyle::FG_WHITE(ConsoleStyle::BG_DARK_BLUE($this->header($input)))),
            self::H2 => ConsoleStyle::BG_BLUE($this->header($input)),
            self::COMMENT => ConsoleStyle::FG_GRAY($this->comment($input)),
            default => $input,
        };
    }

    private function header(string $input): string
    {
        return " {$input} ";
    }

    private function error(string $input): string
    {
        return " {$input} ";
    }

    private function comment(string $input): string
    {
        return implode(
            PHP_EOL,
            [
                '/*',
                ...array_map(
                    fn (string $line) => "* {$line}",
                    explode(PHP_EOL, $input),
                ),
                '*/',
            ],
        );
    }
}
