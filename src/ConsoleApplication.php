<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\AppConfig;
use Tempest\Application;
use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Container\Container;
use Tempest\Kernel;

final readonly class ConsoleApplication implements Application
{
    public function __construct(
        private Container $container,
        private ConsoleArgumentBag $argumentBag,
    ) {
    }

    public static function boot(string $name = 'Tempest', ?AppConfig $appConfig = null): self
    {
        $appConfig ??= new AppConfig(root: getcwd());

        $kernel = new Kernel(
            appConfig: $appConfig,
        );

        $container = $kernel->init();

        $application = $container->get(ConsoleApplication::class);

        // Application-specific config
        $consoleConfig = $container->get(ConsoleConfig::class);
        $consoleConfig->name = $name;

        $appConfig->exceptionHandlers[] = $container->get(ConsoleExceptionHandler::class);

        return $application;
    }

    public function run(): void
    {
        ($this->container->get(ExecuteConsoleCommand::class))($this->argumentBag->getCommandName());
    }
}
