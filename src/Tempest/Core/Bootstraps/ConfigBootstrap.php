<?php

declare(strict_types=1);

namespace Tempest\Core\Bootstraps;

use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\Kernel;
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;
use Tempest\Support\PathHelper;

final readonly class ConfigBootstrap implements Bootstrap
{
    public function __construct(
        private Kernel $kernel,
        private Container $container,
    ) {
    }

    #[EventHandler(KernelEvent::BOOTED)]
    public function boot(): void
    {
        ld('hi');
        // Scan for config files in all discovery locations
        foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
            $configFiles = glob(PathHelper::make($discoveryLocation->path, 'Config/**.php'));

            foreach ($configFiles as $configFile) {
                $configFile = require $configFile;

                $this->container->config($configFile);
            }
        }
    }
}
