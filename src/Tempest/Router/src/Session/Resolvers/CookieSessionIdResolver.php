<?php

declare(strict_types=1);

namespace Tempest\Router\Session\Resolvers;

use Symfony\Component\Uid\Uuid;
use Tempest\Clock\Clock;
use Tempest\Router\Cookie\CookieManager;
use Tempest\Router\Session\Session;
use Tempest\Router\Session\SessionConfig;
use Tempest\Router\Session\SessionId;
use Tempest\Router\Session\SessionIdResolver;

final readonly class CookieSessionIdResolver implements SessionIdResolver
{
    public function __construct(
        private CookieManager $cookies,
        private SessionConfig $sessionConfig,
        private Clock $clock,
    ) {
    }

    public function resolve(): SessionId
    {
        $id = $this->cookies->get(Session::ID)?->value ?? null;

        if (! $id) {
            $id = (string) Uuid::v4();

            $this->cookies->set(
                key: Session::ID,
                value: $id,
                expiresAt: $this->clock->time() + $this->sessionConfig->expirationInSeconds,
            );
        }

        return new SessionId($id);
    }
}
