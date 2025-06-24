<?php

namespace Tempest\Http\Session\Config;

use Tempest\Container\Container;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\Resolvers\CookieSessionIdResolver;
use Tempest\Http\Session\SessionConfig;

final class FileSessionConfig implements SessionConfig
{
    public function __construct(
        /**
         * Path to the sessions storage directory, relative to the internal storage.
         */
        public string $path,

        public Duration $expiration,

        public string $sessionIdResolver = CookieSessionIdResolver::class,
    ) {}

    public function createManager(Container $container): FileSessionManager
    {
        return $container->get(FileSessionManager::class);
    }
}
