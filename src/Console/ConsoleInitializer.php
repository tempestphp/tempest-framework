<?php

namespace Tempest\Console;

use Tempest\Interface\Console;
use Tempest\Interface\Container;
use Tempest\Interface\Initializer;

class ConsoleInitializer implements Initializer
{
    public function initialize(string $className, Container $container): object
    {
        $formatter = new GenericConsoleFormatter();

        $console = new GenericConsole(
            new GenericConsoleOutput($formatter),
            $formatter,
        );

        $container->singleton(Console::class, fn () => $console);

        return $console;
    }
}