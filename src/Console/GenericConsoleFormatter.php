<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Interface\ConsoleFormatter;

final readonly class GenericConsoleFormatter implements ConsoleFormatter
{
    use BaseConsoleFormatter;
}
