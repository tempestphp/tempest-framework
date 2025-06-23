<?php

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\SessionIdResolver;

interface SessionConfig
{
    /**
     * Time required for a session to expire. Defaults to 2 hours.
     */
    public Duration $expiration {
        get;
    }

    /**
     * Class responsible for resolving the session identifier.
     *
     * @var class-string<SessionIdResolver>
     */
    public string $sessionIdResolver {
        get;
    }

    public function createManager(Container $container): SessionManager;
}
