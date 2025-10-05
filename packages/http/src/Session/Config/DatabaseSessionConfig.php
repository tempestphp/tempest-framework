<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Config;

use Tempest\Container\Container;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\Managers\DatabaseSessionManager;
use Tempest\Http\Session\SessionConfig;

final class DatabaseSessionConfig implements SessionConfig
{
    /**
     * @param Duration $expiration Time required for a session to expire.
     */
    public function __construct(
        private(set) Duration $expiration,
    ) {}

    public function createManager(Container $container): DatabaseSessionManager
    {
        return $container->get(DatabaseSessionManager::class);
    }
}
