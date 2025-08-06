<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Resolvers;

use Symfony\Component\Uid\Uuid;
use Tempest\Clock\Clock;
use Tempest\Core\AppConfig;
use Tempest\Http\Cookie\Cookie;
use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Cookie\SameSite;
use Tempest\Http\Request;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

use function Tempest\Support\str;

final readonly class CookieSessionIdResolver implements SessionIdResolver
{
    public function __construct(
        private AppConfig $appConfig,
        private Request $request,
        private CookieManager $cookies,
        private SessionConfig $sessionConfig,
        private Clock $clock,
    ) {}

    public function resolve(): SessionId
    {
        $sessionKey = str($this->appConfig->name ?? 'tempest')
            ->snake()
            ->append('_session_id')
            ->toString();

        $id = $this->request->getCookie($sessionKey)?->value;

        if (! $id) {
            $id = (string) Uuid::v4();

            $this->cookies->add(new Cookie(
                key: $sessionKey,
                value: $id,
                expiresAt: $this->clock->now()->plus($this->sessionConfig->expiration),
                path: '/',
                secure: str($this->appConfig->baseUri)->startsWith('https'),
                httpOnly: true,
                sameSite: SameSite::LAX,
            ));
        }

        return new SessionId($id);
    }
}
