<?php

namespace Tempest\Upgrade\Tests\Tempest320\Fixtures;

use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionIdResolver;
use Tempest\Http\Session\SessionManager;

final class ExistingSessionManager implements SessionManager
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

    public function regenerate(Session $session): void
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

final class ExistingSessionIdResolver implements SessionIdResolver
{
    public function resolve(): SessionId
    {
        return new SessionId('id');
    }

    public function issueNewId(): SessionId
    {
        return new SessionId('id');
    }
}
