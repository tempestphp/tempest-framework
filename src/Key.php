<?php

declare(strict_types=1);

namespace Tempest\Console;

enum Key: string
{
    case UP = '[A';
    case DOWN = '[B';
    case LEFT = '[D';
    case RIGHT = '[C';
    case ENTER = "\n";
}
