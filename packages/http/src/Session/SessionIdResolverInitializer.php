<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\Container\Initializer;
use Tempest\Http\Session\SessionConfig;

final readonly class SessionIdResolverInitializer implements Initializer
{
    public function initialize(Container $container): SessionIdResolver
    {
        $config = $container->get(SessionConfig::class);

        return $container->get($config->sessionIdResolver);
    }
}
