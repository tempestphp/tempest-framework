<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Container\Singleton;

final readonly class SessionManagerInitializer implements Initializer
{
    #[Singleton]
    public function initialize(Container $container): SessionManager
    {
        $config = $container->get(SessionConfig::class);

        return $container->get($config->managerClass);
    }
}
