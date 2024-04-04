<?php

declare(strict_types=1);

namespace Tempest\Console\Inititalizers;

use Tempest\Console\Console;
use Tempest\Console\ConsoleInput;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\GenericConsole;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

#[Singleton]
class ConsoleInitializer implements Initializer
{
    public function initialize(Container $container): Console
    {
        return new GenericConsole(
            $container->get(ConsoleInput::class),
            $container->get(ConsoleOutput::class),
        );
    }
}
