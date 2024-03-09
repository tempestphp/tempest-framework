<?php

declare(strict_types=1);

namespace Tempest\Application\Bootstrap;

use Tempest\Application\BootstrapsKernel;
use Tempest\Application\Kernel;
use Tempest\Container\Container;
use function Tempest\path;

final class LoadConfigurations implements BootstrapsKernel
{
    public function bootstrap(Kernel $kernel): void
    {
        $container = $kernel->getContainer();

        foreach ($kernel->getConfigurationPaths() as $configurationPath) {
            $this->loadConfigurationsFromPath($container, $configurationPath);
        }
    }

    private function loadConfigurationsFromPath(Container $container, string $path): void
    {
        $configFiles = glob(path($path, '*.php'));

        foreach ($configFiles as $configFile) {
            $config = require $configFile;

            $container->config($config);
        }
    }
}
