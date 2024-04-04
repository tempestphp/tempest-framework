<?php

declare(strict_types=1);

namespace Tempest\Console\Testing\Console;

use Tempest\Console\ConsoleApplication;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Container\Container;
use Tempest\CoreConfig;

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
        $coreConfig = $this->container->get(CoreConfig::class);

        $coreConfig->exceptionHandlers[] = $this->container->get(ConsoleExceptionHandler::class);

        $application = new ConsoleApplication(
            args: ['tempest', ...explode(' ', $command)],
            container: $this->container,
            coreConfig: $coreConfig,
        );

        $application->run();

        return new TestConsoleHelper(
            $this->container->get(ConsoleOutput::class),
        );
    }
}
