<?php

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\DateTime\Duration;

interface SessionConfig
{
    /**
     * Time required for a session to expire.
     */
    public Duration $expiration {
        get;
    }

    public function createManager(Container $container): SessionManager;
}
