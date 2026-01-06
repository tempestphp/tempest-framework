<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\DateTime\FormatPattern;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionCreated;
use Tempest\Http\Session\SessionDeleted;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;

use function Tempest\Database\query;
use function Tempest\event;

final readonly class DatabaseSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private SessionConfig $config,
    ) {}

    public function getOrCreate(SessionId $id): Session
    {
        $now = $this->clock->now();
        $session = $this->load($id);

        if ($session === null) {
            $session = new Session(
                id: $id,
                createdAt: $now,
                lastActiveAt: $now,
            );

            event(new SessionCreated($session));
        }

        return $session;
    }

    public function save(Session $session): void
    {
        $session->lastActiveAt = $this->clock->now();

        $existing = query(DatabaseSession::class)
            ->select()
            ->where('id', (string) $session->id)
            ->first();

        if ($existing === null) {
            query(DatabaseSession::class)
                ->insert(
                    id: (string) $session->id,
                    data: serialize($session->data),
                    created_at: $session->createdAt,
                    last_active_at: $session->lastActiveAt,
                )
                ->execute();

            return;
        }

        query(DatabaseSession::class)
            ->update(
                data: serialize($session->data),
                last_active_at: $session->lastActiveAt,
            )
            ->where('id', (string) $session->id)
            ->execute();
    }

    public function delete(Session $session): void
    {
        query(DatabaseSession::class)
            ->delete()
            ->where('id', (string) $session->id)
            ->execute();

        event(new SessionDeleted($session->id));
    }

    public function isValid(Session $session): bool
    {
        return $this->clock->now()->before(
            other: $session->lastActiveAt->plus($this->config->expiration),
        );
    }

    public function deleteExpiredSessions(): void
    {
        $expired = $this->clock
            ->now()
            ->minus($this->config->expiration);

        $expiredSessions = query(DatabaseSession::class)
            ->select()
            ->where('last_active_at < ?', $expired->format(FormatPattern::SQL_DATE_TIME))
            ->all();

        foreach ($expiredSessions as $expiredSession) {
            query(DatabaseSession::class)
                ->delete()
                ->where('id', $expiredSession->id)
                ->execute();

            event(new SessionDeleted(new SessionId((string) $expiredSession->id)));
        }
    }

    private function load(SessionId $id): ?Session
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('id', (string) $id)
            ->first();

        if ($session === null) {
            return null;
        }

        return new Session(
            id: $id,
            createdAt: $session->created_at,
            lastActiveAt: $session->last_active_at,
            data: unserialize($session->data),
        );
    }
}
