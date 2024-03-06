<?php

declare(strict_types=1);

namespace Tempest\Application\Bootstrap;

use Dotenv\Dotenv;
use Tempest\Application\BootstrapsKernel;
use Tempest\Application\Kernel;

/**
 * This bootstrapper loads the environment variables from a
 * ".env" file in our root directory.
 */
final class LoadEnvironmentVariables implements BootstrapsKernel
{
    public function bootstrap(Kernel $kernel): void
    {
        $environment = Dotenv::createUnsafeImmutable($kernel->getRootDirectory());

        $environment->safeLoad();
    }
}
