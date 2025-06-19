<?php

namespace Tempest\Http\Session\Config;

use Tempest\Container\Container;
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

        public int $expirationInSeconds = 60 * 60 * 24 * 30,

        public string $idResolverClass = CookieSessionIdResolver::class,
    ) {}

    public function createManager(Container $container): FileSessionManager
    {
        return $container->get(FileSessionManager::class);
    }
}
