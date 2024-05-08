<?php

declare(strict_types=1);

namespace Tempest\Console;

use ReflectionMethod;
use Tempest\Console\Middleware\ConsoleExceptionMiddleware;
use Tempest\Console\Middleware\HelpMiddleware;

final class ConsoleConfig
{
    public function __construct(
        public string $name = 'Tempest',

        /** @var ConsoleCommand[] $commands */
        public array $commands = [],
        public ?string $logPath = null,

        /** @var array<array-key, class-string<\Tempest\Console\Middleware\ConsoleMiddleware>> */
        public array $middleware = [
            ConsoleExceptionMiddleware::class,
            HelpMiddleware::class,
        ],
    ) {
    }

    public function addCommand(ReflectionMethod $handler, ConsoleCommand $consoleCommand): self
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
