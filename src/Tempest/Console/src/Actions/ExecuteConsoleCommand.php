<?php

declare(strict_types=1);

namespace Tempest\Console\Actions;

use Tempest\Container\Container;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Initializers\Invocation;
use Tempest\Console\GeneratorCommandFactory;
use Tempest\Console\GeneratorCommand;
use Tempest\Console\ExitCode;
use Tempest\Console\ConsoleInputBuilder;
use Tempest\Console\ConsoleConfig;
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

    private function getCallable(array $commandMiddleware): \Closure
    {
        $callable = function (Invocation $invocation) {
            $consoleCommand = $invocation->consoleCommand;
            $inputBuilder   = new ConsoleInputBuilder($consoleCommand, $invocation->argumentBag);

            if ( $consoleCommand instanceof GeneratorCommand ) {
                $handlerClassInstance = $this->container->get(GeneratorCommandFactory::class);
                $handler              = $handlerClassInstance->makeHandler($consoleCommand);
            } else {
                $handlerClassInstance = $consoleCommand;
                $handler              = $consoleCommand->handler;
            }
            
            match (true) {
                is_callable($handler)                 => $handler($inputBuilder->build()),
                ($handler instanceof MethodReflector) => $handler->invokeArgs($handlerClassInstance, $inputBuilder->build()),
                default                               => throw new \RuntimeException('Command handler cannot be resolved.'),
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
