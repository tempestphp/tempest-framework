<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Closure;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleInputBuilder;
use Tempest\Console\Invocation;
use Tempest\Container\Container;
use function Tempest\type;

final readonly class ExecuteConsoleCommand
{
    public function __construct(
        private Container $container,
        private ConsoleConfig $consoleConfig,
        private ConsoleArgumentBag $argumentBag,
    ) {
    }

    public function __invoke(string $commandName): void
    {
        $callable = $this->getCallable($this->resolveCommandMiddleware($commandName));

        $this->argumentBag->setCommandName($commandName);

        $callable(new Invocation(
            argumentBag: $this->argumentBag,
        ));
    }

    private function getCallable(array $commandMiddleware): Closure
    {
        $callable = function (Invocation $invocation) {
            $consoleCommand = $invocation->consoleCommand;

            $handler = $consoleCommand->handler;

            $consoleCommandClass = $this->container->get(type($handler->getDeclaringClass()));

            $inputBuilder = new ConsoleInputBuilder($consoleCommand, $invocation->argumentBag);

            $consoleCommand->handler->invoke(
                $consoleCommandClass,
                ...$inputBuilder->build(),
            );
        };

        $middlewareStack = [...$this->consoleConfig->middleware, ...$commandMiddleware];

        while ($middlewareClass = array_pop($middlewareStack)) {
            $callable = fn (Invocation $invocation) => $this->container->get($middlewareClass)($invocation, $callable);
        }

        return $callable;
    }

    private function resolveCommandMiddleware(string $commandName): array
    {
        $consoleCommand = $this->consoleConfig->commands[$commandName] ?? null;

        return $consoleCommand->middleware ?? [];
    }
}
