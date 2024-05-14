<?php

declare(strict_types=1);

namespace Tempest\Console;

enum ExitCode: int
{
    case SUCCESS = 0;
    case ERROR = 1;
    case INVALID = 2;
    case CANCELLED = 25;
}
