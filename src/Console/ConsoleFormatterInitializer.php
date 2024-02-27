<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final readonly class ConsoleFormatterInitializer implements Initializer
{
    public function initialize(Container $container): object
    {
        $consoleFormatter = $container->get(GenericConsoleFormatter::class);

        $container->singleton(ConsoleFormatter::class, fn () => $consoleFormatter);

        return $consoleFormatter;
    }
}
