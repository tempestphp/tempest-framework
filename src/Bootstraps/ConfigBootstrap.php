<?php

declare(strict_types=1);

namespace Tempest\Bootstraps;

use Tempest\CoreConfig;
use Tempest\Container\Container;
use Tempest\Support\PathHelper;

final readonly class ConfigBootstrap implements Bootstrap
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function boot(): void
    {
        // Scan for config files in all discovery locations
        foreach ($this->container->get(CoreConfig::class)->discoveryLocations as $discoveryLocation) {
            $configFiles = glob(PathHelper::make($discoveryLocation->path, 'Config/**.php'));

            foreach ($configFiles as $configFile) {
                $configFile = require $configFile;

                $this->container->config($configFile);
            }
        }
    }
}
