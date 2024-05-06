<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\AppConfig;
use Tempest\Application;
use Tempest\Console\Actions\RenderConsoleCommandHelp;
use Tempest\Console\Actions\RenderConsoleCommandOverview;
use Tempest\Console\Exceptions\CommandNotFoundException;
use Tempest\Console\Exceptions\ConsoleException;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\Exceptions\MistypedCommandException;
use Tempest\Container\Container;
use Tempest\Kernel;
use Throwable;

final readonly class ConsoleApplication implements Application
{
    public function __construct(
        public ConsoleArgumentBag $argumentBag,
        private Container $container,
        private AppConfig $appConfig,
    ) {
    }

    public static function boot(string $name = 'Tempest', ?AppConfig $appConfig = null): self
    {
        $appConfig ??= new AppConfig(root: getcwd());

        $kernel = new Kernel(
            appConfig: $appConfig,
        );

        $container = $kernel->init();

        $argumentBag = new ConsoleArgumentBag($_SERVER['argv']);

        $container->singleton(ConsoleArgumentBag::class, fn () => $argumentBag);

        $application = new self(
            argumentBag: $argumentBag,
            container: $container,
            appConfig: $appConfig,
        );

        $container->singleton(Application::class, fn () => $application);

        $consoleConfig = $container->get(ConsoleConfig::class);
        $consoleConfig->name = $name;

        $appConfig->exceptionHandlers[] = $container->get(ConsoleExceptionHandler::class);

        return $application;
    }

    public function run(): void
    {
        $commandName = $this->argumentBag->getCommandName();

        if (! $commandName) {
            $this->container->get(RenderConsoleCommandOverview::class)();

            return;
        }

        try {
            $this->executeCommand($commandName);
        } catch (MistypedCommandException $e) {
            $this->executeCommand(
                $e->intendedCommand,
            );
        }
    }

    private function executeCommand(string $commandName): void
    {
        try {
            $config = $this->container->get(ConsoleConfig::class);

            $consoleCommand = $config->commands[$commandName] ?? null;

            if (! $consoleCommand) {
                throw new CommandNotFoundException(
                    commandName: $commandName,
                    consoleConfig: $config,
                );
            }

            if ($this->argumentBag->get('-h') || $this->argumentBag->get('help')) {
                $this->container->get(RenderConsoleCommandHelp::class)($consoleCommand);

                return;
            }

            $handler = $consoleCommand->handler;

            $commandClass = $this->container->get($handler->getDeclaringClass()->getName());

            $inputBuilder = new ConsoleInputBuilder($consoleCommand, $this->argumentBag);

            $handler->invoke(
                $commandClass,
                ...$inputBuilder->build(),
            );
        } catch (ConsoleException $consoleException) {
            $consoleException->render($this->container->get(Console::class));
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
}
