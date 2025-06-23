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
        return $container
            ->get(SessionConfig::class)
            ->createManager($container);
    }
}
