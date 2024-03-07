<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Resolvers;

use Tempest\Http\Cookie\CookieManager;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

final readonly class CookieSessionIdResolver implements SessionIdResolver
{
    private const string SESSION_ID = 'tempest_session_id';

    public function __construct(
        private CookieManager $cookies,
    ) {
    }

    public function resolve(): SessionId
    {
        $id = $this->cookies->get(self::SESSION_ID) ?? null;

        if (! $id) {
            $id = uniqid();

            // TODO: session expiration time support
            $this->cookies->set(self::SESSION_ID, $id);
        }

        return new SessionId($id);
    }
}
