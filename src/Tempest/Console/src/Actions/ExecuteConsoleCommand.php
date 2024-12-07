<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleInputBuilder;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Throwable;

final readonly class ExecuteConsoleCommand
{
    public function __construct(
        private Container $container,
        private ConsoleConfig $consoleConfig,
        private ConsoleArgumentBag $argumentBag,
        private ResolveConsoleCommand $resolveConsoleCommand,
    ) {
    }

    public function __invoke(string|array $command, string|array $arguments = []): ExitCode|int
    {
        [$commandName, $arguments] = $this->resolveCommandAndArguments($command, $arguments);

        $consoleCommand = $this->resolveConsoleCommand($command) ?? $this->resolveConsoleCommand($commandName);
        $callable = $this->getCallable($consoleCommand?->middleware ?? []);

        $this->argumentBag->setCommandName($consoleCommand?->getName() ?? $commandName);
        $this->argumentBag->addMany($arguments);

        return $callable(new Invocation(
            argumentBag: $this->argumentBag,
            consoleCommand: $consoleCommand,
        ));
    }

    private function getCallable(array $commandMiddleware): ConsoleMiddlewareCallable
    {
        $callable = new ConsoleMiddlewareCallable(function (Invocation $invocation) {
            $consoleCommand = $invocation->consoleCommand;

            $consoleCommandClass = $this->container->get($consoleCommand->handler->getDeclaringClass()->getName());

            $inputBuilder = new ConsoleInputBuilder($consoleCommand, $invocation->argumentBag);

            $exitCode = $consoleCommand->handler->invokeArgs(
                $consoleCommandClass,
                $inputBuilder->build(),
            );

            return $exitCode ?? ExitCode::SUCCESS;
        });

        $middlewareStack = [...$this->consoleConfig->middleware, ...$commandMiddleware];

        while ($middlewareClass = array_pop($middlewareStack)) {
            $callable = new ConsoleMiddlewareCallable(
                fn (Invocation $invocation) => $this->container->get($middlewareClass)($invocation, $callable),
            );
        }

        return $callable;
    }

    private function resolveConsoleCommand(string|array $commandName): ?ConsoleCommand
    {
        try {
            return ($this->resolveConsoleCommand)($commandName);
        } catch (Throwable) {
            return null;
        }
    }

    /** @return array{string,array} */
    private function resolveCommandAndArguments(string|array $command, array $arguments = []): array
    {
        $commandName = $command;

        if (is_array($command)) {
            $commandName = $command[0] ?? '';
        } elseif (str_contains($command, ' ')) {
            $commandName = explode(' ', $command)[0];
            $arguments = [
                ...(array_slice(explode(' ', trim($command)), offset: 1)),
                ...$arguments,
            ];
        }

        return [
            $commandName,
            $arguments,
        ];
    }
}
