<?php

declare(strict_types=1);

namespace Tempest\Application;

use Exception;
use ReflectionMethod;
use Tempest\Console\ConsoleConfig;
use Tempest\Interface\Application;
use Tempest\Interface\Container;

final readonly class ConsoleApplication implements Application
{
    public function __construct(
        private array $args,
        private Container $container,
    ) {}

    public function run(): void
    {
        $commandName = $this->args[1] ?? null;

        if (! $commandName) {
            throw new Exception("No command passed");
        }

        $this->handleCommand($commandName);
    }

    private function handleCommand(string $commandName): void
    {
        $config = $this->container->get(ConsoleConfig::class);

        $handler = $config->handlers[$commandName] ?? null;

        if (! $handler) {
            throw new Exception("Command {$commandName} not found");
        }

        $params = $this->resolveParameters($handler);

        /** @var \Tempest\Interface\ConsoleCommand $commandClass */
        $commandClass = $this->container->get($handler->getDeclaringClass()->getName());

        $handler->invoke($commandClass, ...$params);
    }

    private function resolveParameters(ReflectionMethod $handler): array
    {
        $parameters = [];

        $inputArguments = $this->args;

        unset($inputArguments[0], $inputArguments[1]);

        $inputArguments = array_values($inputArguments);

        foreach ($handler->getParameters() as $i => $parameter) {
            $parameters[$parameter->getName()] = $inputArguments[$i];
        }

        return $parameters;
    }
}
