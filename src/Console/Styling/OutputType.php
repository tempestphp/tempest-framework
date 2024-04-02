<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

enum OutputType
{
    case Comment;
    case Brand;
    case Info;
    case Success;
    case Warning;
    case Error;
    case Muted;
    case Squiggly;
    case Formatted;
}
