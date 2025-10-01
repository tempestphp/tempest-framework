<?php

namespace Tempest\Auth\AccessControl;

use Tempest\Auth\AuthConfig;
use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final class AccessControlInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): AccessControl
    {
        return new PolicyBasedAccessControl(
            container: $container,
            authConfig: $container->get(AuthConfig::class),
        );
    }
}
