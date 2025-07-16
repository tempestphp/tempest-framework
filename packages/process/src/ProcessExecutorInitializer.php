<?php

namespace Tempest\Process;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class ProcessExecutorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ProcessExecutor
    {
        return new GenericProcessExecutor();
    }
}
