<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Core\Middleware;
use Tempest\Reflection\MethodReflector;

final class ConsoleConfig
{
    /**
     * List of registered console commands.
     *
     * @var ConsoleCommand[] $commands
     */
    public array $commands = [];

    /**
     * The path to the log file where console output will be recorded.
     */
    public ?string $logPath = null;

    /**
     * Middleware stack for console commands.
     *
     * @see https://tempestphp.com/current/essentials/console-commands#middleware
     *
     * @var Middleware<\Tempest\Console\ConsoleMiddleware>
     */
    public Middleware $middleware {
        get => $this->middleware ??= new Middleware();
    }

    /**
     * @param ?string $name The name of the application. Will appear in console command menus.
     * @param bool $loadBuiltInCommands Whether to load built-in Tempest commands.
     */
    public function __construct(
        public ?string $name = null,
        public bool $loadBuiltInCommands = true,
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
