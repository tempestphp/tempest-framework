<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\Database\Database;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionCache;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionDestroyed;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;

use function Tempest\Database\query;
use function Tempest\event;

final readonly class DatabaseSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private SessionConfig $config,
        private Database $database,
        private SessionCache $cache,
    ) {}

    public function create(SessionId $id): Session
    {
        $session = $this->resolve(id: $id);

        if ($session) {
            return $session;
        }

        $session = new Session(
            id: $id,
            createdAt: $this->clock->now(),
            lastActiveAt: $this->clock->now(),
            data: [],
        );

        $this->cache->store(session: $session);

        return $session;
    }

    public function destroy(SessionId $id): void
    {
        query(model: DatabaseSession::class)
            ->delete()
            ->where('session_id', (string) $id)
            ->execute();

        event(event: new SessionDestroyed(id: $id));
    }

    public function cleanup(): void
    {
        $expired = $this->clock
            ->now()
            ->minus(duration: $this->config->expiration);

        query(model: DatabaseSession::class)
            ->delete()
            ->whereBefore(field: 'last_active_at', date: $expired)
            ->execute();
    }

    public function resolve(SessionId $id): ?Session
    {
        $session = $this->cache->find(sessionId: $id);

        if ($session) {
            return $session;
        }

        $session = query(model: DatabaseSession::class)
            ->select()
            ->where('session_id', (string) $id)
            ->first();

        if (! $session) {
            return null;
        }

        $session = new Session(
            id: $id,
            createdAt: $session->created_at,
            lastActiveAt: $session->last_active_at,
            data: unserialize(data: $session->data),
        );

        $this->cache->store(session: $session);

        return $session;
    }

    public function persist(Session $session): void
    {
        query(model: DatabaseSession::class)->updateOrCreate(find: [
            'session_id' => (string) $session->id,
        ], update: [
            'data' => serialize(value: $session->data),
            'created_at' => $session->createdAt,
            'last_active_at' => $this->clock->now(),
        ]);
    }
}
