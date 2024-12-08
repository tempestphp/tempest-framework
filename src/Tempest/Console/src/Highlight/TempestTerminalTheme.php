<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\TerminalTheme;
use Tempest\Highlight\Themes\EscapesTerminalTheme;
use Tempest\Highlight\Themes\TerminalStyle;
use Tempest\Highlight\Tokens\TokenType;
use Tempest\Highlight\Tokens\TokenTypeEnum;

final readonly class TempestTerminalTheme implements TerminalTheme
{
    use EscapesTerminalTheme;

    public function before(TokenType $tokenType): string
    {
        if ($tokenType instanceof DynamicTokenType) {
            return $this->style($tokenType->getBeforeStyle());
        }

        return match ($tokenType) {
            TokenTypeEnum::KEYWORD => $this->style(TerminalStyle::FG_DARK_BLUE),
            TokenTypeEnum::PROPERTY => $this->style(TerminalStyle::FG_DARK_GREEN),
            TokenTypeEnum::TYPE => $this->style(TerminalStyle::FG_DARK_RED),
            TokenTypeEnum::GENERIC => $this->style(TerminalStyle::FG_DARK_CYAN),
            TokenTypeEnum::VALUE => $this->style(TerminalStyle::FG_BLACK),
            TokenTypeEnum::COMMENT => $this->style(TerminalStyle::FG_GRAY),
            ConsoleTokenType::H1 => $this->style(TerminalStyle::BOLD, TerminalStyle::FG_WHITE, TerminalStyle::BG_DARK_BLUE) . ' ',
            ConsoleTokenType::H2 => $this->style(TerminalStyle::BOLD, TerminalStyle::BG_BLUE) . ' ',
            ConsoleTokenType::EM => $this->style(TerminalStyle::BOLD, TerminalStyle::UNDERLINE),
            ConsoleTokenType::STRONG => $this->style(TerminalStyle::BOLD),
            ConsoleTokenType::UNDERLINE => $this->style(TerminalStyle::UNDERLINE),
            default => TerminalStyle::RESET(),
        };
    }

    public function after(TokenType $tokenType): string
    {
        if ($tokenType instanceof DynamicTokenType) {
            return $this->style($tokenType->getAfterStyle());
        }

        return match ($tokenType) {
            ConsoleTokenType::H2,
            ConsoleTokenType::H1 => ' ' . TerminalStyle::RESET(),
            ConsoleTokenType::EM => $this->style(TerminalStyle::RESET_INTENSITY, TerminalStyle::RESET_UNDERLINE),
            default => TerminalStyle::RESET(),
        };
    }

    private function style(TerminalStyle ...$styles): string
    {
        return implode(
            '',
            array_map(
                fn (TerminalStyle $style) => TerminalStyle::ESC->value .  $style->value,
                $styles,
            ),
        );
    }
}
