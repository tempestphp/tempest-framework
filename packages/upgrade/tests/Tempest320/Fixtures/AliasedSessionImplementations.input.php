<?php

namespace Tempest\Upgrade\Tests\Tempest320\Fixtures;

use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver as BaseSessionIdResolver;
use Tempest\Http\Session\SessionManager as BaseSessionManager;

final class AliasedSessionManager implements BaseSessionManager
{
    public function getOrCreate(SessionId $id): Session
    {
        return new Session($id, createdAt: null);
    }

    public function save(Session $session): void
    {
    }

    public function delete(Session $session): void
    {
    }

    public function isValid(Session $session): bool
    {
        return true;
    }

    public function deleteExpiredSessions(): void
    {
    }
}

final class AliasedSessionIdResolver implements BaseSessionIdResolver
{
    public function resolve(): SessionId
    {
        return new SessionId('id');
    }
}
