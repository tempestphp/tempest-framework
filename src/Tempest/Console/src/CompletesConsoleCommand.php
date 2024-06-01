<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Input\ConsoleArgumentBag;

interface CompletesConsoleCommand
{
    public function complete(
        ConsoleCommand $command,
        ConsoleArgumentBag $argumentBag,
        int $current,
    ): array;
}
