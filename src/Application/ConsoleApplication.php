<?php

declare(strict_types=1);

namespace Tempest\Application;

use ArgumentCountError;
use Tempest\AppConfig;
use Tempest\Console\Argument;
use Tempest\Console\ArgumentBag;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleConfig;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\ConsoleStyle;
use Tempest\Console\ExitException;
use Tempest\Console\InjectedArgument;
use Tempest\Console\RenderConsoleCommand;
use Tempest\Console\RenderConsoleCommandOverview;
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

        $parameters = $this->args->resolveParameters($consoleCommandConfig);

        try {
            array_map(
                fn (InjectedArgument $argument) => $argument->handle($consoleCommandConfig),
                array_filter($parameters->injectedArguments, fn (InjectedArgument $argument) => $argument->shouldInject()),
            );

            $validator = new NestedValidator();
            $validator->validate($parameters);

            $commandClass = $this->container->get($handler->getDeclaringClass()->getName());

            $handler->invoke(
                $commandClass,
                ...array_map(fn (Argument $el) => $el->getValue(), $parameters->arguments),
            );
        } catch (ExitException $e) {
            $this->exit($e);
        } catch (ValidationException $e) {
            $this->handleValidationFailed($consoleCommandConfig, $e);
        } catch (ArgumentCountError $e) {
            $this->handleFailingCommand();
        }
    }

    private function handleFailingCommand(): void
    {
        $output = $this->container->get(ConsoleOutput::class);

        $output->error('Something went wrong');
    }

    private function handleValidationFailed(ConsoleCommand $command, ValidationException $e): void
    {
        $output = $this->container->get(ConsoleOutput::class);

        $output->error(' Validation failed ');

        [$command, $validation] = $this->container->get(RenderConsoleCommand::class)($command, true, errorParts: $e->failingRules);

        $output->writeln("");
        $output->writeln($command);
        $output->writeln($validation);
        $output->writeln("");

        $output->error(sprintf(" Found %s errors ", count($e->failingRules)));
        $output->writeln("");

        foreach ($e->failingRules as $property => $rules) {
            $output->writeln(ConsoleStyle::FG_BLUE($property) . ':');

            foreach ($rules as $rule) {
                $output->writeln(' - ' . $rule->message());
            }
        }
    }

    private function exit(ExitException $e): void
    {
        if ($e->getMessage()) {
            $output = $this->container->get(ConsoleOutput::class);

            $output->writeln($e->getMessage());
        }
    }
}
