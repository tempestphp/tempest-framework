<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Resolvers;

use Symfony\Component\Uid\Uuid;
use Tempest\Core\AppConfig;
use Tempest\Http\Request;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;

use function Tempest\Support\str;

final readonly class HeaderSessionIdResolver implements SessionIdResolver
{
    public function __construct(
        private AppConfig $appConfig,
        private Request $request,
    ) {}

    public function resolve(): SessionId
    {
        $sessionKey = str($this->appConfig->name ?? 'tempest')
            ->snake()
            ->append('_session_id')
            ->toString();

        return new SessionId(
            id: $this->request->headers[$sessionKey] ?? Uuid::v4()->toString(),
        );
    }
}
