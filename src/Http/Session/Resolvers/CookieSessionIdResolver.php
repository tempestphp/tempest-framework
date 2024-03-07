<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Resolvers;

use Tempest\Clock\Clock;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

final readonly class CookieSessionIdResolver implements SessionIdResolver
{
    private const string SESSION_ID = 'tempest_session_id';

    public function __construct(
        private CookieManager $cookies,
        private SessionConfig $sessionConfig,
        private Clock $clock,
    ) {
    }

    public function resolve(): SessionId
    {
        $id = $this->cookies->get(self::SESSION_ID)?->value ?? null;

        if (! $id) {
            $id = uniqid();

            $this->cookies->set(
                key: self::SESSION_ID,
                value: $id,
                expiresAt: $this->clock->time() + $this->sessionConfig->expirationInSeconds
            );
        }

        return new SessionId($id);
    }
}
