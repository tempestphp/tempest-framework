<?php

declare(strict_types=1);

namespace Tempest\Console;

enum ConsoleStyle: string
{
    case ESC = "\033[";
    case RESET = "0m";

    case FG_BLACK = "30m";
    case FG_DARK_RED = "31m";
    case FG_DARK_GREEN = "32m";
    case FG_DARK_YELLOW = "33m";
    case FG_DARK_BLUE = "34m";
    case FG_DARK_MAGENTA = "35m";
    case FG_DARK_CYAN = "36m";
    case FG_LIGHT_GRAY = "37m";
    case FG_GRAY = "90m";
    case FG_RED = "91m";
    case FG_GREEN = "92m";
    case FG_YELLOW = "93m";
    case FG_BLUE = "94m";
    case FG_MAGENTA = "95m";
    case FG_CYAN = "96m";
    case FG_WHITE = "97m";

    case BG_BLACK = "40m";
    case BG_DARK_RED = "41m";
    case BG_DARK_GREEN = "42m";
    case BG_DARK_YELLOW = "43m";
    case BG_DARK_BLUE = "44m";
    case BG_DARK_MAGENTA = "45m";
    case BG_DARK_CYAN = "46m";
    case BG_LIGHT_GRAY = "47m";
    case BG_GRAY = "100m";
    case BG_RED = "101m";
    case BG_GREEN = "102m";
    case BG_YELLOW = "103m";
    case BG_BLUE = "104m";
    case BG_MAGENTA = "105m";
    case BG_CYAN = "106m";
    case BG_WHITE = "107m";

    case BOLD = "1m";
    case UNDERLINE = "4m";
    case NO_UNDERLINE = "24m";
    case REVERSE_TEXT = "7m";
    case NON_REVERSE_TEXT = "27m";

    public static function RESET(string $text = ''): string
    {
        return $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_BLACK(string $text = ''): string
    {
        return self::ESC->value . self::FG_BLACK->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_DARK_RED(string $text = ''): string
    {
        return self::ESC->value . self::FG_DARK_RED->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_DARK_GREEN(string $text = ''): string
    {
        return self::ESC->value . self::FG_DARK_GREEN->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_DARK_YELLOW(string $text = ''): string
    {
        return self::ESC->value . self::FG_DARK_YELLOW->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_DARK_BLUE(string $text = ''): string
    {
        return self::ESC->value . self::FG_DARK_BLUE->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_DARK_MAGENTA(string $text = ''): string
    {
        return self::ESC->value . self::FG_DARK_MAGENTA->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_DARK_CYAN(string $text = ''): string
    {
        return self::ESC->value . self::FG_DARK_CYAN->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_LIGHT_GRAY(string $text = ''): string
    {
        return self::ESC->value . self::FG_LIGHT_GRAY->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_GRAY(string $text = ''): string
    {
        return self::ESC->value . self::FG_GRAY->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_RED(string $text = ''): string
    {
        return self::ESC->value . self::FG_RED->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_GREEN(string $text = ''): string
    {
        return self::ESC->value . self::FG_GREEN->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_YELLOW(string $text = ''): string
    {
        return self::ESC->value . self::FG_YELLOW->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_BLUE(string $text = ''): string
    {
        return self::ESC->value . self::FG_BLUE->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_MAGENTA(string $text = ''): string
    {
        return self::ESC->value . self::FG_MAGENTA->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_CYAN(string $text = ''): string
    {
        return self::ESC->value . self::FG_CYAN->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function FG_WHITE(string $text = ''): string
    {
        return self::ESC->value . self::FG_WHITE->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_BLACK(string $text = ''): string
    {
        return self::ESC->value . self::BG_BLACK->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_DARK_RED(string $text = ''): string
    {
        return self::ESC->value . self::BG_DARK_RED->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_DARK_GREEN(string $text = ''): string
    {
        return self::ESC->value . self::BG_DARK_GREEN->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_DARK_YELLOW(string $text = ''): string
    {
        return self::ESC->value . self::BG_DARK_YELLOW->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_DARK_BLUE(string $text = ''): string
    {
        return self::ESC->value . self::BG_DARK_BLUE->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_DARK_MAGENTA(string $text = ''): string
    {
        return self::ESC->value . self::BG_DARK_MAGENTA->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_DARK_CYAN(string $text = ''): string
    {
        return self::ESC->value . self::BG_DARK_CYAN->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_LIGHT_GRAY(string $text = ''): string
    {
        return self::ESC->value . self::BG_LIGHT_GRAY->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_GRAY(string $text = ''): string
    {
        return self::ESC->value . self::BG_GRAY->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_RED(string $text = ''): string
    {
        return self::ESC->value . self::BG_RED->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_GREEN(string $text = ''): string
    {
        return self::ESC->value . self::BG_GREEN->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_YELLOW(string $text = ''): string
    {
        return self::ESC->value . self::BG_YELLOW->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_BLUE(string $text = ''): string
    {
        return self::ESC->value . self::BG_BLUE->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_MAGENTA(string $text = ''): string
    {
        return self::ESC->value . self::BG_MAGENTA->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_CYAN(string $text = ''): string
    {
        return self::ESC->value . self::BG_CYAN->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BG_WHITE(string $text = ''): string
    {
        return self::ESC->value . self::BG_WHITE->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function BOLD(string $text = ''): string
    {
        return self::ESC->value . self::BOLD->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function UNDERLINE(string $text = ''): string
    {
        return self::ESC->value . self::UNDERLINE->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function NO_UNDERLINE(string $text = ''): string
    {
        return self::ESC->value . self::NO_UNDERLINE->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function REVERSE_TEXT(string $text = ''): string
    {
        return self::ESC->value . self::REVERSE_TEXT->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }

    public static function NON_REVERSE_TEXT(string $text = ''): string
    {
        return self::ESC->value . self::NON_REVERSE_TEXT->value . $text . ConsoleStyle::ESC->value . ConsoleStyle::RESET->value;
    }
}
