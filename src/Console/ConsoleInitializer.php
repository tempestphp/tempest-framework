<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

class ConsoleInitializer implements Initializer
{
    public function initialize(string $className, Container $container): object
    {
        $console = new GenericConsole(
            $container->get(ConsoleInput::class),
            $container->get(ConsoleOutput::class),
        );

        $container->singleton(Console::class, fn () => $console);

        return $console;
    }
}
