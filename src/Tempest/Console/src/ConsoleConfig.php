<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Middleware\ConsoleExceptionMiddleware;
use Tempest\Console\Middleware\HelpMiddleware;
use Tempest\Console\Middleware\InvalidCommandMiddleware;
use Tempest\Console\Middleware\OverviewMiddleware;
use Tempest\Console\Middleware\ResolveOrRescueMiddleware;
use Tempest\Reflection\MethodReflector;

final class ConsoleConfig
{
    public function __construct(
        public string $name = 'Tempest',

        /** @var ConsoleCommand[] $commands */
        public array $commands = [],
        public ?string $logPath = null,

        /** @var array<array-key, class-string<\Tempest\Console\ConsoleMiddleware>> */
        public array $middleware = [
            OverviewMiddleware::class,
            ConsoleExceptionMiddleware::class,
            ResolveOrRescueMiddleware::class,
            InvalidCommandMiddleware::class,
            HelpMiddleware::class,
        ],
    ) {
    }

    public function addCommand(MethodReflector $handler, ConsoleCommand $consoleCommand): self
    {
        $consoleCommand->setHandler($handler);

        $this->commands[$consoleCommand->getName()] = $consoleCommand;

        foreach ($consoleCommand->aliases as $alias) {
            if (array_key_exists($alias, $this->commands)) {
                continue;
            }

            $this->commands[$alias] = $consoleCommand;
        }

        return $this;
    }
}
