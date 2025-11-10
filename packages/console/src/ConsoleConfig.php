<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Core\Middleware;
use Tempest\Reflection\MethodReflector;

final class ConsoleConfig
{
    public function __construct(
        public string $name = 'Tempest',

        /** @var ConsoleCommand[] $commands */
        public array $commands = [],
        public ?string $logPath = null,

        /** @var Middleware<\Tempest\Console\ConsoleMiddleware> */
        public Middleware $middleware = new Middleware(),
    ) {}

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
