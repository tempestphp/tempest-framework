<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Interface\Console;
use Tempest\Interface\ConsoleFormatter;
use Tempest\Interface\ConsoleOutput;

class GenericConsole implements Console
{
    use BaseConsoleInput;
    use BaseConsoleOutput;

    public function __construct(
        private readonly ConsoleOutput $output,
        private readonly ConsoleFormatter $formatter,
    ) {
    }
}
