<?php

declare(strict_types=1);

namespace Tempest\Core\Kernel;

use Tempest\Core\Kernel;
use Tempest\Support\PathHelper;

/** @internal */
final readonly class LoadConfig
{
    public function __construct(
        private Kernel $kernel,
    ) {
    }

    public function __invoke(): void
    {
        // Scan for config files in all discovery locations
        foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
            $configFiles = glob(PathHelper::make($discoveryLocation->path, 'Config/**.php'));

            foreach ($configFiles as $configFile) {
                $configFile = require $configFile;

                $this->kernel->container->config($configFile);
            }
        }
    }
}
