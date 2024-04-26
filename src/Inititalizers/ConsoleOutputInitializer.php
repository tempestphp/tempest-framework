<?php

declare(strict_types=1);

namespace Tempest\Console\Inititalizers;

use Tempest\Application;
use Tempest\Console\ConsoleApplication;
use Tempest\Console\ConsoleOutput;
use Tempest\Console\GenericConsoleOutput;
use Tempest\Console\NullConsoleOutput;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Highlight\Highlighter;

#[Singleton]
final readonly class ConsoleOutputInitializer implements Initializer
{
    public function initialize(Container $container): ConsoleOutput
    {
        $app = $container->get(Application::class);

        if (! $app instanceof ConsoleApplication) {
            $consoleOutput = new NullConsoleOutput();
        } else {
            $consoleOutput = new GenericConsoleOutput($container->get(Highlighter::class));
        }

        return $consoleOutput;
    }
}
