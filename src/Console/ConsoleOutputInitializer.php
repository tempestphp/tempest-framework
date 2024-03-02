<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class ConsoleOutputInitializer implements Initializer
{
    public function initialize(Container $container): ConsoleOutput
    {
        $app = $container->get(Application::class);

        if (! $app instanceof ConsoleApplication) {
            $consoleOutput = new NullConsoleOutput();
        } else {
            $consoleOutput = new GenericConsoleOutput();
        }

        return $consoleOutput;
    }
}
