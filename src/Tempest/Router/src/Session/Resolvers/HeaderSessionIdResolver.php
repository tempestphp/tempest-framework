<?php

declare(strict_types=1);

namespace Tempest\Router\Session\Resolvers;

use Symfony\Component\Uid\Uuid;
use Tempest\Router\Request;
use Tempest\Router\Session\Session;
use Tempest\Router\Session\SessionId;
use Tempest\Router\Session\SessionIdResolver;

final readonly class HeaderSessionIdResolver implements SessionIdResolver
{
    public function __construct(
        private Request $request,
    ) {}

    public function resolve(): SessionId
    {
        $id = $this->request->headers[Session::ID] ?? null;

        return new SessionId($id ?? ((string) Uuid::v4()));
    }
}
