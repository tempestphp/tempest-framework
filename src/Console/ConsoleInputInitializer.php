<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Application\ConsoleApplication;
use Tempest\Interface\Application;
use Tempest\Interface\ConsoleInput;
use Tempest\Interface\ConsoleOutput;
use Tempest\Interface\Container;
use Tempest\Interface\Initializer;

final readonly class ConsoleInputInitializer implements Initializer
{
    public function initialize(string $className, Container $container): object
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
