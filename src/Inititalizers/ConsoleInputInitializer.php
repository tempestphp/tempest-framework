<?php

declare(strict_types=1);

namespace Tempest\Console\Inititalizers;

use Tempest\Application;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\ConsoleInput;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\GenericConsoleInput;
use Tempest\Console\NullConsoleInput;
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
