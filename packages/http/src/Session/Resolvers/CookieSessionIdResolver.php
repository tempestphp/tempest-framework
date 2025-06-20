<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Resolvers;

use Symfony\Component\Uid\Uuid;
use Tempest\Clock\Clock;
use Tempest\Core\AppConfig;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

use function Tempest\Support\str;

final readonly class CookieSessionIdResolver implements SessionIdResolver
{
    public function __construct(
        private AppConfig $appConfig,
        private CookieManager $cookies,
        private SessionConfig $sessionConfig,
        private Clock $clock,
    ) {}

    public function resolve(): SessionId
    {
        $sessionId = str($this->appConfig->name ?? 'tempest')
            ->snake()
            ->append('_session_id')
            ->toString();

        $id = $this->cookies->get($sessionId)->value ?? null;

        if (! $id) {
            $id = (string) Uuid::v4();

            $this->cookies->add(new Cookie(
                key: $sessionId,
                value: $id,
                expiresAt: $this->clock->now()->plus($this->sessionConfig->expiration),
            ));
        }

        return new SessionId($id);
    }
}
