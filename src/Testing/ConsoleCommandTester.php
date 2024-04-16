<?php

declare(strict_types=1);

namespace Tempest\Console\Testing;

use Tempest\AppConfig;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\ConsoleComponentRenderer;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Container\Container;

final readonly class ConsoleCommandTester
{
    public function __construct(private Container $container)
    {
    }

    public function call(string $command): TestConsoleHelper
    {
        $appConfig = $this->container->get(AppConfig::class);

        $this->container->singleton(
            ConsoleOutput::class,
            fn () => new TestConsoleOutput(),
        );

        $this->container->singleton(
            ConsoleComponentRenderer::class,
            fn () => new TestConsoleComponentRenderer(),
        );

        $appConfig->exceptionHandlers[] = $this->container->get(ConsoleExceptionHandler::class);

        $application = new ConsoleApplication(
            argumentBag: new ConsoleArgumentBag(['tempest', ...explode(' ', $command)]),
            container: $this->container,
            appConfig: $appConfig,
        );

        $application->run();

        return new TestConsoleHelper(
            $this->container->get(ConsoleOutput::class),
            $this->container->get(ConsoleComponentRenderer::class),
        );
    }
}
