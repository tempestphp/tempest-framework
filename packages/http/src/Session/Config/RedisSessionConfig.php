<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Config;

use Tempest\Container\Container;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\Managers\RedisSessionManager;
use Tempest\Http\Session\SessionConfig;

final class RedisSessionConfig implements SessionConfig
{
    /**
     * @param Duration $expiration Time required for a session to expire.
     */
    public function __construct(
        private(set) Duration $expiration,
        readonly string $prefix = 'session:',
    ) {}

    public function createManager(Container $container): RedisSessionManager
    {
        return $container->get(RedisSessionManager::class);
    }
}
