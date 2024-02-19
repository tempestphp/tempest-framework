<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Application\ConsoleApplication;
use Tempest\Interface\Application;
use Tempest\Interface\ConsoleOutput;
use Tempest\Interface\Container;
use Tempest\Interface\Initializer;

final readonly class ConsoleOutputInitializer implements Initializer
{
    public function initialize(string $className, Container $container): object
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
