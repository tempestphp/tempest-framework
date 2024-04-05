<?php

declare(strict_types=1);

namespace Tempest\Console;

use Tempest\Container\Container;
use Tempest\Container\Initializer;

final class ConsoleOutputBuilderInitializer implements Initializer
{
    public function initialize(Container $container): ConsoleOutputBuilder
    {
        return new ConsoleOutputBuilder(
            $container->get(ConsoleOutput::class),
        );
    }
}
