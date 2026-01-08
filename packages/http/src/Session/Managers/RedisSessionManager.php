<?php

declare(strict_types=1);

namespace Tempest\Http\Session\Managers;

use Tempest\Clock\Clock;
use Tempest\Http\Session\Config\RedisSessionConfig;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionCreated;
use Tempest\Http\Session\SessionDeleted;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\KeyValue\Redis\Redis;
use Tempest\Support\Str;
use Throwable;

use function Tempest\event;

final readonly class RedisSessionManager implements SessionManager
{
    public function __construct(
        private Clock $clock,
        private Redis $redis,
        private RedisSessionConfig $config,
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

        $this->redis->set(
            key: $this->getKey($session->id),
            value: serialize($session),
            expiration: $this->config->expiration,
        );
    }

    public function delete(Session $session): void
    {
        $this->redis->command('UNLINK', $this->getKey($session->id));

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
        $cursor = '0';

        do {
            /** @var array<int,string> $keys */
            [$cursor, $keys] = $this->redis->command('SCAN', $cursor, 'MATCH', "{$this->config->prefix}*", 'COUNT', '100');

            foreach ($keys as $key) {
                $sessionId = $this->getSessionIdFromKey($key);
                $session = $this->load($sessionId);

                if ($session === null) {
                    continue;
                }

                if ($this->isValid($session)) {
                    continue;
                }

                $this->delete($session);
            }
        } while ($cursor !== '0');
    }

    private function load(SessionId $id): ?Session
    {
        try {
            return unserialize(
                data: $this->redis->get($this->getKey($id)),
                options: ['allowed_classes' => true],
            );
        } catch (Throwable) {
            return null;
        }
    }

    private function getKey(SessionId $id): string
    {
        return sprintf('%s%s', $this->config->prefix, $id);
    }

    private function getSessionIdFromKey(string $key): SessionId
    {
        return new SessionId(Str\after_first($key, $this->config->prefix));
    }
}
