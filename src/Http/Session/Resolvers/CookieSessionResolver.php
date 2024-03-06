<?php

namespace Tempest\Http\Session\Resolvers;

use Tempest\Http\Request;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionResolver;

final readonly class CookieSessionResolver implements SessionResolver
{
    private const string SESSION_ID = 'tempest_session_id';

    public function __construct(
        private Request $request,
    ) {}

    public function resolve(): SessionId
    {
        $id = $this->request->getCookies()[self::SESSION_ID];

        if (! $id) {
            // TODO generate new ID and store in a cookie
        }

        return new SessionId($id);
    }
}