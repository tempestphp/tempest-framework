<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionDestroyed;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\KeyValue\Redis\Redis;
use Tempest\Support\Filesystem;
use Throwable;
use function Tempest\event;

final readonly class RedisSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private Redis $redis,
        private SessionConfig $sessionConfig,
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
        return $this->getData($id)[$key] ?? $default;
    }

    public function remove(SessionId $id, string $key): void
    {
        $data = $this->getData($id);

        unset($data[$key]);

        $this->persist($id, $data);
    }

    public function destroy(SessionId $id): void
    {
        $this->redis->getClient()->del($this->getKey($id));

        event(new SessionDestroyed($id));
    }

    public function isValid(SessionId $id): bool
    {
        $session = $this->resolve($id);

        if ($session === null) {
            return false;
        }

        if (! ($session->lastActiveAt ?? null)) {
            return false;
        }

        return $this->clock->now()->before(
            other: $session->lastActiveAt->plus($this->sessionConfig->expiration),
        );
    }

    private function resolve(SessionId $id): ?Session
    {
        try {
            $content = $this->redis->get($this->getKey($id));
            return unserialize($content, ['allowed_classes' => true]);
        } catch (Throwable $e) {
            return null;
        }
    }

    public function all(SessionId $id): array
    {
        return $this->getData($id);
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

        $session->lastActiveAt = $now;

        if ($data !== null) {
            $session->data = $data;
        }

        $this->redis->set($this->getKey($id), serialize($session), $this->sessionConfig->expiration);

        return $session;
    }

    private function getKey(SessionId $id): string
    {
        return sprintf('%s_%s', $this->sessionConfig->prefix, $id);
    }

    public function cleanup(): void
    {
        // what should we do here?
        // on persist we set the expiration (ttl) for the session in the redis store
        // in theory all session data should be expire by itself
    }
}
