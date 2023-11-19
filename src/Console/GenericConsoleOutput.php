<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Interface\ConsoleOutput;

final readonly class GenericConsoleOutput implements ConsoleOutput
{
    use BaseConsoleOutput;
}
