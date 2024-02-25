<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class ConsoleInputInitializer implements Initializer
{
    public function initialize(Container $container): object
    {
        $app = $container->get(Application::class);

        if (! $app instanceof ConsoleApplication) {
            return new NullConsoleInput();
        }

        $consoleInput = new GenericConsoleInput($container->get(ConsoleOutput::class));

        $container->singleton(ConsoleInput::class, fn () => $consoleInput);

        return $consoleInput;
    }
}
