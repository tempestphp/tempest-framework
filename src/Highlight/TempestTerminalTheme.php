<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\TerminalTheme;
use Tempest\Highlight\Themes\EscapesTerminalTheme;
use Tempest\Highlight\Themes\TerminalStyle;
use Tempest\Highlight\Tokens\TokenType;

final readonly class TempestTerminalTheme implements TerminalTheme
{
    use EscapesTerminalTheme;

    public function before(TokenType $tokenType): string
    {
        return match ($tokenType) {
            ConsoleTokenType::H1 => $this->style(TerminalStyle::BOLD, TerminalStyle::FG_WHITE, TerminalStyle::BG_DARK_BLUE) . ' ',
            ConsoleTokenType::H2 => $this->style(TerminalStyle::BOLD, TerminalStyle::BG_BLUE) . ' ',
            ConsoleTokenType::ERROR => $this->style(TerminalStyle::BOLD, TerminalStyle::FG_WHITE, TerminalStyle::BG_RED) . ' ',
            ConsoleTokenType::QUESTION => $this->style(TerminalStyle::BG_BLUE) . ' ',
            ConsoleTokenType::SUCCESS => $this->style(TerminalStyle::BG_DARK_GREEN) . ' ',
            ConsoleTokenType::COMMENT => $this->style(TerminalStyle::FG_GRAY),
            ConsoleTokenType::EM => $this->style(TerminalStyle::FG_BLUE),
            ConsoleTokenType::STRONG => $this->style(TerminalStyle::BOLD),
            ConsoleTokenType::UNDERLINE => $this->style(TerminalStyle::UNDERLINE),
            default => TerminalStyle::RESET(),
        };
    }

    public function after(TokenType $tokenType): string
    {
        return match ($tokenType) {
            ConsoleTokenType::ERROR,
            ConsoleTokenType::QUESTION,
            ConsoleTokenType::H2,
            ConsoleTokenType::H1,
            ConsoleTokenType::SUCCESS => ' ',
            default => '',
        } . TerminalStyle::RESET();
    }

    private function style(TerminalStyle ...$styles): string
    {
        return implode(
            '',
            array_map(
                fn (TerminalStyle $style) => TerminalStyle::ESC->value . $style->value,
                $styles,
            ),
        );
    }
}
