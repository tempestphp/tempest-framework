<?php

declare(strict_types=1);

namespace Tempest\Console\Inititalizers;

use Tempest\Console\Console;
use Tempest\Console\ConsoleComponentRenderer;
use Tempest\Console\Terminal\GenericConsoleComponentRenderer;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
final readonly class ConsoleComponentRendererInitializer implements Initializer
{
    public function initialize(Container $container): ConsoleComponentRenderer
    {
        return new GenericConsoleComponentRenderer($container->get(Console::class));
    }
}
