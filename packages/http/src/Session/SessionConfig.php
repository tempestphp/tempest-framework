<?php

namespace Tempest\Http\Session;

use Tempest\Container\Container;
use Tempest\DateTime\Duration;

interface SessionConfig
{
    /**
     * Time required for a session to expire.
     */
    public Duration $expiration { get; }

    /**
     * Defines the strategy used to clean up expired sessions. The default strategy is `RANDOM_REQUESTS`, which provides a good balance between performance and cleanup frequency.
     */
    public CleanupStrategy $cleanupStrategy { get; }

    public function createManager(Container $container): SessionManager;
}
