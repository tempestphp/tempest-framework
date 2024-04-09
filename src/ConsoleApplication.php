<?php

declare(strict_types=1);

namespace Tempest\Console;

use ArgumentCountError;
use Tempest\AppConfig;
use Tempest\Application;
use Tempest\Console\Actions\RenderConsoleCommandOverview;
use Tempest\Console\Exceptions\CommandNotFoundException;
use Tempest\Console\Exceptions\ConsoleException;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\Exceptions\InvalidCommandException;
use Tempest\Container\Container;
use Tempest\Kernel;
use Throwable;

final readonly class ConsoleApplication implements Application
{
    public static function boot(
        string $name = 'Tempest',
        ?AppConfig $appConfig = null,
    ): self {
        $appConfig ??= new AppConfig(root: getcwd());

        $kernel = new Kernel(
            appConfig: $appConfig,
        );

        $container = $kernel->init();

        $application = new self(
            argumentBag: new ConsoleArgumentBag($_SERVER['argv']),
            container: $container,
            appConfig: $appConfig,
        );

        $container->singleton(Application::class, fn () => $application);

        $appConfig->exceptionHandlers[] = $container->get(ConsoleExceptionHandler::class);

        $consoleConfig = $container->get(ConsoleConfig::class);
        $consoleConfig->name = $name;

        return $application;
    }

    public function __construct(
        private ConsoleArgumentBag $argumentBag,
        private Container $container,
        private AppConfig $appConfig,
    ) {
    }

    public function run(): void
    {
        try {
            $commandName = $this->argumentBag->getCommandName();

            if (! $commandName) {
                $this->container->get(RenderConsoleCommandOverview::class)();

                return;
            }

            $this->handleCommand($commandName);
        } catch (ConsoleException $consoleException) {
            $consoleException->render($this->container->get(ConsoleOutput::class));
        } catch (Throwable $throwable) {
            if (
                ! $this->appConfig->enableExceptionHandling
                || $this->appConfig->exceptionHandlers === []
            ) {
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

        $consoleCommand = $config->commands[$commandName] ?? null;

        if (! $consoleCommand) {
            throw new CommandNotFoundException($commandName);
        }

        $handler = $consoleCommand->handler;

        $commandClass = $this->container->get($handler->getDeclaringClass()->getName());

        try {
            $handler->invoke(
                $commandClass,
                ...$this->buildInput($consoleCommand),
            );
        } catch (ArgumentCountError) {
            throw new InvalidCommandException($commandName, $consoleCommand);
        }
    }

    /**
     * Returns resolved key-value pair of parameters.
     *
     * @return array<string, mixed>
     */
    private function buildInput(ConsoleCommand $command): array
    {
        $builder = new ConsoleInputBuilder(
            $command->getDefinition(),
            $this->argumentBag,
        );

        return $builder->build();
    }
}
