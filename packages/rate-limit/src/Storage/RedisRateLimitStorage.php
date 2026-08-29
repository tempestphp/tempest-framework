<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Storage;

use Tempest\Clock\Clock;
use Tempest\DateTime\Duration;
use Tempest\KeyValue\Redis\Redis;
use Tempest\RateLimit\Config\RateLimitConfig;
use Tempest\RateLimit\RateLimitStorage;

/**
 * Stores rate limit windows in Redis. Atomic scripts eliminate the need for locks.
 */
final readonly class RedisRateLimitStorage implements RateLimitStorage
{
    /**
     * Increments the counter and opens a window if none exists. A window opens only if TTL < 0,
     * not when hits reset: a reset counter doesn't get a second window.
     */
    private const string INCREMENT = <<<'LUA'
    local hits = redis.call('INCRBY', KEYS[1], ARGV[2])
    local ttl = redis.call('TTL', KEYS[1])

    if ttl < 0 then
        redis.call('EXPIRE', KEYS[1], ARGV[1])
        ttl = tonumber(ARGV[1])
    end

    return {hits, ttl}
    LUA;

    /**
     * Returns {hits, ttl} or false if no window is open.
     */
    private const string FIND = <<<'LUA'
    local hits = redis.call('GET', KEYS[1])

    if not hits then
        return false
    end

    return {tonumber(hits), redis.call('TTL', KEYS[1])}
    LUA;

    public function __construct(
        private Redis $redis,
        private Clock $clock,
        private RateLimitConfig $config,
    ) {}

    public function find(string $key): ?RateLimitState
    {
        return $this->toState($this->eval(self::FIND, $key));
    }

    public function increment(string $key, Duration $window, int $by = 1): RateLimitState
    {
        $windowInSeconds = RateLimitState::windowInSeconds($window);

        return $this->toState($this->eval(self::INCREMENT, $key, (string) $windowInSeconds, (string) $by)) ?? throw RateLimitStorageFailed::redisDidNotReportAWindow($key);
    }

    public function remove(string $key): void
    {
        $this->redis->command('DEL', $this->config->storageKey($key));
    }

    /**
     * Runs one of the scripts above against a single key. Raw commands bypass the client's prefix. The
     * key is derived here.
     *
     * Scripts are sent with `EVAL` rather than cached with `EVALSHA`, as they are a couple of hundred
     * bytes and the supported clients disagree on how a missing script is signalled.
     */
    private function eval(string $script, string $key, string ...$arguments): mixed
    {
        return $this->redis->command('EVAL', $script, '1', $this->config->storageKey($key), ...$arguments);
    }

    /**
     * @param mixed $reply The `{hits, ttl}` pair replied by one of the scripts, or `false` when no window is open.
     */
    private function toState(mixed $reply): ?RateLimitState
    {
        if (! is_array($reply)) {
            return null;
        }

        [$hits, $timeToLiveInSeconds] = $reply;

        return new RateLimitState(
            hits: (int) $hits,
            resetsAtInSeconds: $this->clock->seconds() + max(0, (int) $timeToLiveInSeconds),
        );
    }
}
