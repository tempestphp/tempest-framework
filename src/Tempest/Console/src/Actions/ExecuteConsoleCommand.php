<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleInputBuilder;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\InvalidExitCode;
use Tempest\Container\Container;

final readonly class ExecuteConsoleCommand
{
    public function __construct(
        private Container $container,
        private ConsoleConfig $consoleConfig,
        private ConsoleArgumentBag $argumentBag,
    ) {
    }

    public function __invoke(string $commandName): ExitCode
    {
        $callable = $this->getCallable($this->resolveCommandMiddleware($commandName));

        $this->argumentBag->setCommandName($commandName);

        return $callable(new Invocation(
            argumentBag: $this->argumentBag,
        ));
    }

    private function getCallable(array $commandMiddleware): ConsoleMiddlewareCallable
    {
        $callable = new ConsoleMiddlewareCallable(function (Invocation $invocation) {
            $consoleCommand = $invocation->consoleCommand;

            $handler = $consoleCommand->handler;

            $consoleCommandClass = $this->container->get($handler->getDeclaringClass()->getName());

            $inputBuilder = new ConsoleInputBuilder($consoleCommand, $invocation->argumentBag);

            $exitCode = $consoleCommand->handler->invokeArgs(
                $consoleCommandClass,
                $inputBuilder->build(),
            );

            if ($exitCode === null) {
                $exitCode = ExitCode::SUCCESS;
            } elseif (is_int($exitCode)) {
                $exitCode = ExitCode::tryFrom($exitCode) ?? throw new InvalidExitCode($exitCode);
            }

            return $exitCode;
        });

        $middlewareStack = [...$this->consoleConfig->middleware, ...$commandMiddleware];

        while ($middlewareClass = array_pop($middlewareStack)) {
            $callable = new ConsoleMiddlewareCallable(
                fn (Invocation $invocation) => $this->container->get($middlewareClass)($invocation, $callable),
            );
        }

        return $callable;
    }

    private function resolveCommandMiddleware(string $commandName): array
    {
        $consoleCommand = $this->consoleConfig->commands[$commandName] ?? null;

        return $consoleCommand->middleware ?? [];
    }
}
