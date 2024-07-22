<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Container\Container;
use Tempest\Core\Application\AppConfig;
use Tempest\Core\Application\Application;
use Tempest\Core\Application\Kernel;
use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\LogConfig;
use Tempest\Support\PathHelper;

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

        $logConfig = $container->get(LogConfig::class);
        $logConfig->debugLogPath = PathHelper::make($appConfig->root, '/log/debug.log');
        $logConfig->channels[] = new AppendLogChannel(PathHelper::make($appConfig->root, '/log/tempest.log'));

        $appConfig->exceptionHandlers[] = $container->get(ConsoleExceptionHandler::class);

        return $application;
    }

    public function run(): void
    {
        $exitCode = ($this->container->get(ExecuteConsoleCommand::class))($this->argumentBag->getCommandName());

        exit($exitCode->value);
    }
}
