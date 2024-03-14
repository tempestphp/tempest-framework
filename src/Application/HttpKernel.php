<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\Application\Bootstrap\LoadEnvironmentVariables;
use Tempest\Container\Container;

final class HttpKernel implements Kernel
{
    use IsKernel;

    public function __construct(Container $container, string $basePath)
    {
        $this
            ->setContainer($container)
            ->setBasePath($basePath)
            ->setBootstrappers([
                LoadEnvironmentVariables::class,
            ]);
    }

    public function run(): void
    {
        $this->boot();

        // TODO: Handle stuff.
        dump('Running application...');
        dump($_ENV);

        $this->shutdown();
    }
}
