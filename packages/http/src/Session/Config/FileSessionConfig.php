<?php

namespace Tempest\Http\Session\Config;

use Tempest\Container\Container;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\SessionConfig;

final class FileSessionConfig implements SessionConfig
{
    /**
     * @param string $path Path to the sessions storage directory, relative to the internal storage.
     * @param Duration $expiration Time required for a session to expire.
     */
    public function __construct(
        public Duration $expiration,
        public string $path = 'sessions',
    ) {}

    public function createManager(Container $container): FileSessionManager
    {
        return $container->get(FileSessionManager::class);
    }
}
