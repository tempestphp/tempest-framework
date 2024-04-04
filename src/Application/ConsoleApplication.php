<?php

declare(strict_types=1);

namespace Tempest\Application;

use ArgumentCountError;
use ReflectionMethod;
use Tempest\AppConfig;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\ConsoleCommandInput;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleInputArgument;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\RenderConsoleCommandOverview;
use Tempest\Console\UnresolvedArgumentsException;
use Tempest\Container\Container;
use Throwable;

final readonly class ConsoleApplication implements Application
{
    public function __construct(
        private ConsoleArgumentBag $args,
        private Container $container,
        private AppConfig $appConfig,
    ) {
    }

    public function run(): void
    {
        try {
            $commandName = $this->args->getCommandName();

            $output = $this->container->get(ConsoleOutput::class);

            if (! $commandName) {
                $output->writeln($this->container->get(RenderConsoleCommandOverview::class)());

                return;
            }

            $this->handleCommand($commandName);
        } catch (Throwable $throwable) {
            if (! $this->appConfig->enableExceptionHandling) {
                throw $throwable;
            }

            foreach ($this->appConfig->exceptionHandlers as $exceptionHandler) {
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

        try {
            $parameters = $this->args->resolveArguments($consoleCommandConfig);
        } catch (UnresolvedArgumentsException $exception) {
            $output = $this->container->get(ConsoleOutput::class);

            foreach ($exception->getArguments() as $x) {
                $output->error($x->value . ' is not a valid argument');
            }

            return;
        }

        $this->executeCommand($handler, $parameters);
    }

    public function executeCommand(ReflectionMethod $handler, ConsoleCommandInput $input): void
    {
        $commandClass = $this->container->get($handler->getDeclaringClass()->getName());

        try {
            $handler->invoke(
                $commandClass,
                ...array_map(fn (ConsoleInputArgument $argument) => $argument->getValue(), $input->arguments),
            );
        } catch (ArgumentCountError) {
            $this->handleFailingCommand();
        }
    }

    private function handleFailingCommand(): void
    {
        $output = $this->container->get(ConsoleOutput::class);

        $output->error('Something went wrong');
    }
}
