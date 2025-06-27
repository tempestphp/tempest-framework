<?php

namespace Tempest\Process\Testing;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;
use Tempest\Process\ProcessExecutor;

final class RestrictedProcessExecutorInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): ProcessExecutor
    {
        return new RestrictedProcessExecutor();
    }
}
