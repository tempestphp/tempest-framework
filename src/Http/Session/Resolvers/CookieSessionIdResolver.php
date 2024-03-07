<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Resolvers;

use Tempest\Http\Request;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

final readonly class CookieSessionIdResolver implements SessionIdResolver
{
    private const string SESSION_ID = 'tempest_session_id';

    public function __construct(
        private Request $request,
    ) {
    }

    public function resolve(): SessionId
    {
        $id = $this->request->getCookies()[self::SESSION_ID] ?? null;

        if (! $id) {
            // TODO generate new ID and store in a cookie
        }

        return new SessionId($id);
    }
}
