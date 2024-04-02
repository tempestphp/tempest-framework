<?php

declare(strict_types=1);

namespace Tempest\Console\Styling;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class ConsoleOutputThemeInitializer implements Initializer
{
    public function initialize(Container $container): ConsoleOutputTheme
    {
        return new TempestConsoleOutputTheme();
    }
}
