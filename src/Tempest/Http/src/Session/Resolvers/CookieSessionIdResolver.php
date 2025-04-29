<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Resolvers;

use Symfony\Component\Uid\Uuid;
use Tempest\Clock\Clock;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

final readonly class CookieSessionIdResolver implements SessionIdResolver
{
    public function __construct(
        private CookieManager $cookies,
        private SessionConfig $sessionConfig,
        private Clock $clock,
    ) {}

    public function resolve(): SessionId
    {
        $id = $this->cookies->get(Session::ID)->value ?? null;

        if (! $id) {
            $id = (string) Uuid::v4();

            $this->cookies->set(
                key: Session::ID,
                value: $id,
                expiresAt: $this->clock->now()->plusSeconds($this->sessionConfig->expirationInSeconds),
            );
        }

        return new SessionId($id);
    }
}
