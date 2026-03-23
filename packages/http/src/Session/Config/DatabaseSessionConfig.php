<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Config;

use Tempest\Container\Container;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\CleanupStrategy;
use Tempest\Http\Session\Managers\DatabaseSessionManager;
use Tempest\Http\Session\SessionConfig;

final class DatabaseSessionConfig implements SessionConfig
{
    /**
     * @param Duration $expiration Time required for a session to expire.
     * @param CleanupStrategy $cleanupStrategy Strategy for cleaning up expired sessions. Defaults to `RANDOM_REQUESTS`, which provides a good balance between performance and cleanup frequency.
     */
    public function __construct(
        private(set) Duration $expiration,
        private(set) CleanupStrategy $cleanupStrategy = CleanupStrategy::RANDOM_REQUESTS,
    ) {}

    public function createManager(Container $container): DatabaseSessionManager
    {
        return $container->get(DatabaseSessionManager::class);
    }
}
