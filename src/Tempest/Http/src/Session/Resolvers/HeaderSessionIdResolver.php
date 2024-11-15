<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Resolvers;

use Symfony\Component\Uid\Uuid;
use Tempest\Http\Request;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

final readonly class HeaderSessionIdResolver implements SessionIdResolver
{
    public function __construct(
        private Request $request,
    ) {
    }

    public function resolve(): SessionId
    {
        $id = $this->request->getHeaders()[Session::ID] ?? null;

        return new SessionId($id ?? (string) Uuid::v4());
    }
}
