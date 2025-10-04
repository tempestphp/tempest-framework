<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\Database\Database;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionDestroyed;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Arr;
use Tempest\Support\Arr\ArrayInterface;

use function Tempest\Database\query;
use function Tempest\event;

final readonly class DatabaseSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private SessionConfig $config,
        private Database $database,
    ) {}

    public function create(SessionId $id): Session
    {
        return $this->persist($id);
    }

    public function set(SessionId $id, string $key, mixed $value): void
    {
        $this->persist($id, [...$this->getData($id), ...[$key => $value]]);
    }

    public function get(SessionId $id, string $key, mixed $default = null): mixed
    {
        $value = Arr\get_by_key($this->getData($id), $key, $default);

        if ($value instanceof ArrayInterface) {
            return $value->toArray();
        }

        return $value;
    }

    public function all(SessionId $id): array
    {
        return $this->getData($id);
    }

    public function remove(SessionId $id, string $key): void
    {
        $data = $this->getData($id);
        $data = Arr\remove_keys($data, $key);

        $this->persist($id, $data);
    }

    public function destroy(SessionId $id): void
    {
        query(DatabaseSession::class)
            ->delete()
            ->where('session_id', (string) $id)
            ->execute();

        event(new SessionDestroyed($id));
    }

    public function isValid(SessionId $id): bool
    {
        $session = $this->resolve($id);

        if ($session === null) {
            return false;
        }

        return $this->clock->now()->before(
            other: $session->lastActiveAt->plus($this->config->expiration),
        );
    }

    public function cleanup(): void
    {
        $expired = $this->clock
            ->now()
            ->minus($this->config->expiration);

        query(DatabaseSession::class)
            ->delete()
            ->whereBefore('last_active_at', $expired)
            ->execute();
    }

    private function resolve(SessionId $id): ?Session
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('session_id', (string) $id)
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

    /**
     * @return array<mixed>
     */
    private function getData(SessionId $id): array
    {
        return $this->resolve($id)->data ?? [];
    }

    /**
     * @param array<mixed>|null $data
     */
    private function persist(SessionId $id, ?array $data = null): Session
    {
        $now = $this->clock->now();
        $session = $this->resolve($id) ?? new Session(
            id: $id,
            createdAt: $now,
            lastActiveAt: $now,
        );

        if ($data !== null) {
            $session->data = $data;
        }

        query(DatabaseSession::class)->updateOrCreate([
            'session_id' => (string) $id,
        ], [
            'data' => serialize($session->data),
            'created_at' => $session->createdAt,
            'last_active_at' => $now,
        ]);

        return $session;
    }
}
