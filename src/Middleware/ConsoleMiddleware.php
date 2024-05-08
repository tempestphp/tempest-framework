<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\ConsoleCommand;

interface ConsoleMiddleware
{
    public function __invoke(
        ConsoleCommand $consoleCommand,
        ConsoleArgumentBag $argumentBag,
        callable $next
    ): void;
}
