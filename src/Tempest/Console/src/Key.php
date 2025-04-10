<?php

declare(strict_types=1);

namespace Tempest\Console;

enum Key: string
{
    case UP = "\e[A";
    case DOWN = "\e[B";
    case LEFT = "\e[D";
    case CTRL_LEFT = "\eb";
    case RIGHT = "\e[C";
    case CTRL_RIGHT = "\ef";
    case TAB = "\t";
    case ENTER = "\n";
    case ALT_ENTER = "\e\n";
    case BACKSPACE = "\x7F";
    case CTRL_BACKSPACE = "\x17";
    case DELETE = "\e[3~";
    case CTRL_DELETE = "\ed";
    case SPACE = ' ';
    case CTRL_B = "\x02";
    case CTRL_C = "\x03";
    case CTRL_D = "\x04";
    case START_OF_LINE = "\x01";
    case HOME = "\e[H";
    case END_OF_LINE = "\x05";
    case END = "\e[F";
    case ESCAPE = "\e";
}
