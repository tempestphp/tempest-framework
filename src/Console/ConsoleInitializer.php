<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Interface\Console;
use Tempest\Interface\ConsoleInput;
use Tempest\Interface\ConsoleOutput;
use Tempest\Interface\Container;
use Tempest\Interface\Initializer;

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
