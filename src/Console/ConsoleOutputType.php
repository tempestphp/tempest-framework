<?php

declare(strict_types=1);

namespace Tempest\Console;

enum ConsoleOutputType
{
    case Comment;
    case Brand;
    case Info;
    case Success;
    case Warning;
    case Error;
    case Muted;
    case Formatted;
    case Label;
}
