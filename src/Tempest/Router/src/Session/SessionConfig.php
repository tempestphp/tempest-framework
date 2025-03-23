<?php

declare(strict_types=1);

namespace Tempest\Router\Session;

use Tempest\Router\Session\Managers\FileSessionManager;
use Tempest\Router\Session\Resolvers\CookieSessionIdResolver;

final class SessionConfig
{
    public function __construct(
        /**
         * Path to the sessions storage directory, relative to the internal storage.
         */
        public string $path = 'sessions',

        /**
         * Time required for a session to expire. Defaults to one month.
         */
        public int $expirationInSeconds = 60 * 60 * 24 * 30,

        /**
         * @template SessionManager of \Tempest\Router\Session\SessionManager
         * @var class-string<SessionManager>
         */
        public string $managerClass = FileSessionManager::class,

        /**
         * @template SessionIdResolver of \Tempest\Router\Session\SessionIdResolver
         * @var class-string<SessionIdResolver>
         */
        public string $idResolverClass = CookieSessionIdResolver::class,
    ) {}
}
