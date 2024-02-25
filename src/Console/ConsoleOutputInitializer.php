<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class ConsoleOutputInitializer implements Initializer
{
    public function initialize(Container $container): object
    {
        $app = $container->get(Application::class);

        if (! $app instanceof ConsoleApplication) {
            return new NullConsoleOutput();
        }

        $consoleOutput = new GenericConsoleOutput(new GenericConsoleFormatter());

        $container->singleton(ConsoleOutput::class, fn () => $consoleOutput);

        return $consoleOutput;
    }
}
