<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\Application\Bootstrap\LoadConfigurations;
use Tempest\Application\Bootstrap\LoadEnvironmentVariables;
use Tempest\Container\Container;

final class ConsoleKernel implements Kernel
{
    use IsKernel;

    public function __construct(Container $container, string $basePath)
    {
        $this
            ->setContainer($container)
            ->setBasePath($basePath)
            ->setBootstrappers([
                LoadEnvironmentVariables::class,
                LoadConfigurations::class,
            ]);
    }

    public function run(): void
    {
        $this->boot();

        // TODO: Handle stuff.

        $this->shutdown();
    }
}
