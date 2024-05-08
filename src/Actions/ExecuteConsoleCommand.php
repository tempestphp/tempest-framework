<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Closure;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleInputBuilder;
use Tempest\Console\Middleware\ConsoleExceptionMiddleware;
use Tempest\Console\Middleware\HelpMiddleware;
use Tempest\Container\Container;
use function Tempest\type;

final readonly class ExecuteConsoleCommand
{
    public function __construct(
        private Container $container,
        private ConsoleConfig $consoleConfig,
        private ConsoleArgumentBag $argumentBag,
        private RenderConsoleCommandOverview $renderConsoleCommandOverview,
        private RenderConsoleRescueScreen $renderConsoleRescueScreen,
    ) {
    }

    public function __invoke(string $commandName): void
    {
        if (! $commandName) {
            ($this->renderConsoleCommandOverview)();

            return;
        }

        $consoleCommand = $this->consoleConfig->commands[$commandName] ?? null;

        if (! $consoleCommand) {
            ($this->renderConsoleRescueScreen)($commandName);

            return;
        }

        $callable = $this->getCallable();

        $callable($consoleCommand, $this->argumentBag);
    }

    private function getCallable(): Closure
    {
        $callable = function (ConsoleCommand $consoleCommand, ConsoleArgumentBag $argumentBag) {
            $handler = $consoleCommand->handler;

            $consoleCommandClass = $this->container->get(type($handler->getDeclaringClass()));

            $inputBuilder = new ConsoleInputBuilder($consoleCommand, $argumentBag);

            $consoleCommand->handler->invoke(
                $consoleCommandClass,
                ...$inputBuilder->build(),
            );
        };

        // TODO: move to config
        $middlewareStack = [
            ConsoleExceptionMiddleware::class,
            HelpMiddleware::class,
        ];

        while ($middlewareClass = array_pop($middlewareStack)) {
            $callable = fn (ConsoleCommand $consoleCommand, ConsoleArgumentBag $argumentBag) => $this->container->get($middlewareClass)($consoleCommand, $argumentBag, $callable);
        }

        return $callable;
    }
}
