<?php

declare(strict_types=1);

namespace Tempest\Testing\Console;

use Tempest\AppConfig;
use Tempest\Console\ArgumentBag;
use Tempest\Application\ConsoleApplication;
use Tempest\Console\ConsoleOutput;
use Tempest\Container\Container;
use Tempest\Exceptions\ConsoleExceptionHandler;

final readonly class ConsoleCommandTester
{
    public function __construct(private Container $container)
    {
        $this->container->singleton(
            ConsoleOutput::class,
            fn () => $this->container->get(TestConsoleOutput::class)
        );
    }

    public function call(string $command): TestConsoleHelper
    {
        $appConfig = $this->container->get(AppConfig::class);

        $appConfig->exceptionHandlers[] = $this->container->get(ConsoleExceptionHandler::class);

        $application = new ConsoleApplication(
            args: $this->container->get(ArgumentBag::class, ['tempest', ...explode(' ', $command)]),
            container: $this->container,
            appConfig: $appConfig,
        );

        $application->run();

        return new TestConsoleHelper(
            $this->container->get(ConsoleOutput::class),
        );
    }
}
