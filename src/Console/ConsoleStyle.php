<?php

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
}