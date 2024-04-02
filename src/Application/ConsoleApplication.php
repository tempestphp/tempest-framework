<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\AppConfig;
use Tempest\Console\Argument;
use Tempest\Console\ArgumentBag;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ExitException;
use Tempest\Console\InjectedArgument;
use Tempest\Console\Styling\RenderCommandNotFound;
use Tempest\Console\Styling\RenderConsoleCommandOverview;
use Tempest\Console\Styling\RenderValidationFailed;
use Tempest\Container\Container;
use Tempest\Validation\Exceptions\ValidationException;
use Tempest\Validation\NestedValidator;
use Throwable;

final readonly class ConsoleApplication implements Application
{
    public function __construct(
        private ArgumentBag $args,
        private Container $container,
        private AppConfig $appConfig,
    ) {
    }

    public function run(): void
    {
        $output = $this->container->get(ConsoleOutput::class);

        $commandName = $this->args->getCommandName();

        if (! $commandName) {
            $output->writeln($this->container->get(RenderConsoleCommandOverview::class)());

            return;
        }

        try {
            $this->handleCommand($commandName);
        } catch (CommandNotFound $e) {
            $output->write(
                $this->container->get(RenderCommandNotFound::class)($commandName),
            );
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

        $parameters = $this->args->resolveParameters($consoleCommandConfig);

        try {
            $validator = new NestedValidator();
            $validator->validate($parameters);

            array_map(
                fn (InjectedArgument $argument) => $argument->handle($consoleCommandConfig),
                array_filter($parameters->injectedArguments, fn (InjectedArgument $argument) => $argument->shouldInject()),
            );
        } catch (ExitException $e) {
            $this->exit($e);

            return;
        } catch (ValidationException $e) {
            $output = $this->container->get(ConsoleOutput::class);

            $output->write(
                $this->container->get(RenderValidationFailed::class)($consoleCommandConfig, $e),
            );

            return;
        }

        try {
            $commandClass = $this->container->get($handler->getDeclaringClass()->getName());

            $handler->invoke(
                $commandClass,
                ...array_map(fn (Argument $el) => $el->getValue(), $parameters->arguments),
            );
        } catch (Throwable $e) {
            $this->handleFailingCommand();

            throw $e;
        }
    }

    private function handleFailingCommand(): void
    {
        $output = $this->container->get(ConsoleOutput::class);

        $output->error('Something went wrong');
    }

    private function exit(ExitException $e): void
    {
        if ($e->getMessage()) {
            $output = $this->container->get(ConsoleOutput::class);

            $output->writeln($e->getMessage());
        }
    }
}
