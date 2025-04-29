<?php

declare(strict_types=1);

namespace Tempest\Console\Highlight;

use Tempest\Highlight\Themes\TerminalStyle;
use Tempest\Highlight\Tokens\TokenType;

use function Tempest\Support\str;

final readonly class DynamicTokenType implements TokenType
{
    public function __construct(
        private string $style,
    ) {}

    public function getBeforeStyle(): TerminalStyle
    {
        $normalizedStyle = str($this->style)
            ->lower()
            ->replace(['_', '-'], '');

        foreach (TerminalStyle::cases() as $case) {
            $normalizedCase = str($case->name)
                ->lower()
                ->replace(['_', '-'], '');

            if ($normalizedCase->equals($normalizedStyle)) {
                return $case;
            }
        }

        return TerminalStyle::RESET;
    }

    public function getAfterStyle(): TerminalStyle
    {
        return match ($this->getBeforeStyle()) {
            // Mods
            TerminalStyle::BOLD => TerminalStyle::RESET_INTENSITY,
            TerminalStyle::DIM => TerminalStyle::RESET_INTENSITY,
            TerminalStyle::ITALIC => TerminalStyle::RESET_ITALIC,
            TerminalStyle::HIDDEN => TerminalStyle::VISIBLE,
            TerminalStyle::UNDERLINE => TerminalStyle::RESET_UNDERLINE,
            TerminalStyle::OVERLINE => TerminalStyle::RESET_OVERLINE,
            TerminalStyle::STRIKETHROUGH => TerminalStyle::RESET_STRIKETHROUGH,
            TerminalStyle::REVERSE_TEXT => TerminalStyle::RESET_REVERSE_TEXT,
            // Foregrounds
            TerminalStyle::FG_BLACK,
            TerminalStyle::FG_DARK_RED,
            TerminalStyle::FG_DARK_GREEN,
            TerminalStyle::FG_DARK_YELLOW,
            TerminalStyle::FG_DARK_BLUE,
            TerminalStyle::FG_DARK_MAGENTA,
            TerminalStyle::FG_DARK_CYAN,
            TerminalStyle::FG_LIGHT_GRAY,
            TerminalStyle::FG_GRAY,
            TerminalStyle::FG_RED,
            TerminalStyle::FG_GREEN,
            TerminalStyle::FG_YELLOW,
            TerminalStyle::FG_BLUE,
            TerminalStyle::FG_MAGENTA,
            TerminalStyle::FG_CYAN,
            TerminalStyle::FG_WHITE,
                => TerminalStyle::RESET_FOREGROUND,
            // Backgrounds
            TerminalStyle::BG_BLACK,
            TerminalStyle::BG_DARK_RED,
            TerminalStyle::BG_DARK_GREEN,
            TerminalStyle::BG_DARK_YELLOW,
            TerminalStyle::BG_DARK_BLUE,
            TerminalStyle::BG_DARK_MAGENTA,
            TerminalStyle::BG_DARK_CYAN,
            TerminalStyle::BG_LIGHT_GRAY,
            TerminalStyle::BG_GRAY,
            TerminalStyle::BG_RED,
            TerminalStyle::BG_GREEN,
            TerminalStyle::BG_YELLOW,
            TerminalStyle::BG_BLUE,
            TerminalStyle::BG_MAGENTA,
            TerminalStyle::BG_CYAN,
            TerminalStyle::BG_WHITE,
                => TerminalStyle::RESET_BACKGROUND,
            default => TerminalStyle::RESET,
        };
    }

    public function getValue(): string
    {
        return '';
    }

    public function canContain(TokenType $other): bool
    {
        return false;
    }
}
