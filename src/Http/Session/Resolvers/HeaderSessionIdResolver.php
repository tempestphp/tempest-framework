<?php

namespace Tempest\Http\Session\Resolvers;

use Tempest\Http\Request;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

final readonly class HeaderSessionIdResolver implements SessionIdResolver
{
    private const string SESSION_ID = 'tempest_session_id';

    public function __construct(
        private Request $request,
    ) {}

    public function resolve(): SessionId
    {
        $id = $this->request->getHeaders()[self::SESSION_ID] ?? null;

        return new SessionId($id ?? uniqid());
    }
}