<?php

declare(strict_types=1);

namespace Tempest\Console\Middleware;

use Tempest\Console\Actions\RenderConsoleCommandHelp;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\ConsoleCommand;

final readonly class HelpMiddleware implements ConsoleMiddleware
{
    public function __construct(private RenderConsoleCommandHelp $renderConsoleCommandHelp)
    {
    }

    public function __invoke(ConsoleCommand $consoleCommand, ConsoleArgumentBag $argumentBag, callable $next): void
    {
        if ($argumentBag->get('-h') || $argumentBag->get('help')) {
            ($this->renderConsoleCommandHelp)($consoleCommand);

            return;
        }

        $next($consoleCommand, $argumentBag);
    }
}
