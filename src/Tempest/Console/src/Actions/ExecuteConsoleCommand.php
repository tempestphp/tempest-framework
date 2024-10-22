<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Closure;
use RuntimeException;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleInputBuilder;
use Tempest\Console\ExitCode;
use Tempest\Console\GeneratorCommand;
use Tempest\Console\Initializers\Invocation;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Reflection\MethodReflector;

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

    private function getCallable(array $commandMiddleware): Closure
    {
        $callable = function (Invocation $invocation) {
            $consoleCommand = $invocation->consoleCommand;
            $inputBuilder = new ConsoleInputBuilder($consoleCommand, $invocation->argumentBag);
            $handler = ($consoleCommand instanceof GeneratorCommand)
                ? $consoleCommand->makeHandler()
                : $consoleCommand->handler;

            match (true) {
                is_callable($handler) => $handler($inputBuilder->build()),
                ($handler instanceof MethodReflector) => $handler->invokeArgs($consoleCommand, $inputBuilder->build()),
                default => throw new RuntimeException('Command handler cannot be resolved.'), // @phpstan-ignore-line
            };

            return ExitCode::SUCCESS;
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
