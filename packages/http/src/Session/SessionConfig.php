<?php

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\Http\Session\SessionIdResolver;

interface SessionConfig
{
    /**
     * Time required for a session to expire. Defaults to one month.
     */
    public int $expirationInSeconds {
        get;
    }

    /**
     * @template SessionIdResolver of \Tempest\Http\Session\SessionIdResolver
     * @var class-string<SessionIdResolver>
     */
    public string $idResolverClass {
        get;
    }

    public function createManager(Container $container): SessionManager;
}
