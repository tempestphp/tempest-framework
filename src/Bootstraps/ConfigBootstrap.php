<?php

declare(strict_types=1);

namespace Tempest\Bootstraps;

use Tempest\AppConfig;
use Tempest\Container\Container;
use function Tempest\path;

final readonly class ConfigBootstrap implements Bootstrap
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function boot(): void
    {
        // Scan for config files in all discovery locations
        foreach ($this->container->get(AppConfig::class)->discoveryLocations as $discoveryLocation) {
            $configFiles = glob(path($discoveryLocation->path, 'Config/**.php'));

            foreach ($configFiles as $configFile) {
                $configFile = require $configFile;

                $this->container->config($configFile);
            }
        }
    }
}
