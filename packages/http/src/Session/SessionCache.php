<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Tempest\Clock\Clock;
use Tempest\Container\Singleton;

use function Tempest\get;

#[Singleton]
final class SessionCache
{
    /**
     * @var array<string, Session>
     */
    private array $sessions = [];

    /**
     * @var array<string, Session>
     */
    private array $originalValues = [];

    private SessionManager $manager {
        get => get(className: SessionManager::class);
    }

    public function __construct(
        private readonly Clock $clock,
        private readonly SessionConfig $config,
    ) {}

    public function store(Session $session): void
    {
        $this->sessions[(string) $session->id] = $session;
        $this->originalValues[(string) $session->id] = clone $session;
    }

    public function find(SessionId $sessionId): ?Session
    {
        return $this->sessions[(string) $sessionId] ?? null;
    }

    public function set(SessionId $sessionId, string $key, mixed $value): void
    {
        $this->sessions[(string) $sessionId]->data[$key] = $value;
    }

    public function get(SessionId $sessionId, string $key, mixed $default = null): mixed
    {
        return $this->sessions[(string) $sessionId]->data[$key] ?? $default;
    }

    public function all(SessionId $sessionId): array
    {
        return $this->sessions[(string) $sessionId]->data;
    }

    public function remove(SessionId $sessionId, string $key): void
    {
        unset($this->sessions[(string) $sessionId]->data[$key]);
    }

    public function destroy(SessionId $sessionId): void
    {
        unset($this->sessions[(string) $sessionId]);
        unset($this->originalValues[(string) $sessionId]);

        $this->manager->destroy(id: $sessionId);
    }

    public function persist(SessionId $sessionId): void
    {
        if (! $this->hasChanged(sessionId: $sessionId)) {
            return;
        }

        $session = $this->sessions[(string) $sessionId];

        $this->manager->persist(session: $session);

        $this->originalValues[(string) $sessionId] = clone $session;
    }

    public function isValid(Session $session): bool
    {
        return $this->clock->now()->before(
            other: $session->lastActiveAt->plus(duration: $this->config->expiration),
        );
    }

    private function hasChanged(SessionId $sessionId): bool
    {
        $current = $this->sessions[(string) $sessionId];
        $original = $this->originalValues[(string) $sessionId] ?? null;

        if ($original === null) {
            return true;
        }

        return json_encode(value: $current->data) !== json_encode(value: $original->data);
    }
}
