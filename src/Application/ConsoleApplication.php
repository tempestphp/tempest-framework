<?php

declare(strict_types=1);

namespace Tempest\Application;

use ArgumentCountError;
use Exception;
use ReflectionMethod;
use Tempest\Console\ConsoleConfig;
use Tempest\Interface\Application;
use Tempest\Interface\ConsoleOutput;
use Tempest\Interface\Container;
use Throwable;

final readonly class ConsoleApplication implements Application
{
    public function __construct(
        private array $args,
        private Container $container,
    ) {
    }

    public function run(): void
    {
        $commandName = $this->args[1] ?? null;

        $output = $this->container->get(ConsoleOutput::class);

        if (! $commandName) {
            // TODO: show list of available commands
            $output->success("Hello Tempest");

            return;
        }

        try {
            $this->handleCommand($commandName);
        } catch (Throwable $error) {
            $output->error($error->getMessage());
        }
    }

    private function handleCommand(string $commandName): void
    {
        $config = $this->container->get(ConsoleConfig::class);

        $handler = $config->handlers[$commandName] ?? null;

        if (! $handler) {
            throw new Exception("Command `{$commandName}` not found");
        }

        $params = $this->resolveParameters($handler);

        /** @var \Tempest\Interface\ConsoleCommand $commandClass */
        $commandClass = $this->container->get($handler->getDeclaringClass()->getName());

        try {
            $handler->invoke($commandClass, ...$params);
        } catch (ArgumentCountError) {
            $this->handleFailingCommand();
        }
    }

    private function resolveParameters(ReflectionMethod $handler): array
    {
        $parameters = $handler->getParameters();
        $inputArguments = $this->args;
        unset($inputArguments[0], $inputArguments[1]);
        $inputArguments = array_values($inputArguments);

        $result = [];

        foreach ($inputArguments as $i => $argument) {
            if (str_starts_with($argument, '--')) {
                $parts = explode('=', str_replace('--', '', $argument));

                $key = $parts[0];

                $result[$key] = $parts[1] ?? true;
            } else {
                $key = ($parameters[$i] ?? null)?->getName();

                $result[$key ?? $i] = $argument;
            }
        }

        return $result;
    }

    private function handleFailingCommand(): void
    {
        $output = $this->container->get(ConsoleOutput::class);

        $output->error('Something went wrong');
    }
}
