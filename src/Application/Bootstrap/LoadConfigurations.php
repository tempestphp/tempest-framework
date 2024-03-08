<?php

namespace Tempest\Application\Bootstrap;

use Tempest\Application\BootstrapsKernel;
use Tempest\Application\Kernel;
use function Tempest\path;

final class LoadConfigurations implements BootstrapsKernel
{
    public function bootstrap(Kernel $kernel): void
    {
        $configFiles = glob(path($kernel->getConfigPath(), '*.php'));

        foreach ($configFiles as $configFile) {
            $config = require $configFile;

            $kernel->getContainer()->config($config);
        }
    }
}