<?php

declare(strict_types=1);

namespace Tempest\Console;

use ArgumentCountError;
use ReflectionMethod;
use Tempest\Application;
use Tempest\Console\Actions\RenderConsoleCommandOverview;
use Tempest\Console\Exceptions\CommandNotFound;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Container\Container;
use Tempest\CoreConfig;
use Tempest\Kernel;
use Throwable;

final readonly class ConsoleApplication implements Application
{
    public static function boot(string $root): self
    {
        $coreConfig = new CoreConfig($root);

        $kernel = new Kernel(
            coreConfig: $coreConfig,
        );

        $container = $kernel->init();

        $application = new self(
            args: $_SERVER['argv'],
            container: $container,
            coreConfig: $coreConfig,
        );

        $container->singleton(Application::class, fn () => $application);

        $coreConfig->exceptionHandlers[] = $container->get(ConsoleExceptionHandler::class);

        return $application;
    }

    public function __construct(
        private array $args,
        private Container $container,
        private CoreConfig $coreConfig,
    ) {
    }

    public function run(): void
    {
        try {
            $commandName = $this->args[1] ?? null;

            $output = $this->container->get(ConsoleOutput::class);

            if (! $commandName) {
                $output->writeln($this->container->get(RenderConsoleCommandOverview::class)());

                return;
            }

            $this->handleCommand($commandName);
        } catch (Throwable $throwable) {
            if (! $this->coreConfig->enableExceptionHandling) {
                throw $throwable;
            }

            foreach ($this->coreConfig->exceptionHandlers as $exceptionHandler) {
                $exceptionHandler->handle($throwable);
            }
        }
    }

    private function handleCommand(string $commandName): void
    {
        $config = $this->container->get(ConsoleConfig::class);

        $consoleCommandConfig = $config->commands[$commandName] ?? null;

        if (! $consoleCommandConfig) {
            throw new CommandNotFound($commandName);
        }

        $handler = $consoleCommandConfig->handler;

        $params = $this->resolveParameters($handler);

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
