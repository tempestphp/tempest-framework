<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class ConsoleInputInitializer implements Initializer
{
    public function initialize(Container $container): ConsoleInput
    {
        $app = $container->get(Application::class);

        if (! $app instanceof ConsoleApplication) {
            $consoleInput = new NullConsoleInput();
        } else {
            $consoleInput = new GenericConsoleInput($container->get(ConsoleOutput::class));
        }

        return $consoleInput;
    }
}
