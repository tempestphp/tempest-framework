<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Testing;

use Tempest\Clock\Clock;
use Tempest\DateTime\Duration;
use Tempest\RateLimit\RateLimitStorage;
use Tempest\RateLimit\Storage\RateLimitState;

/**
 * In-memory rate limit storage for testing. Windows expire based on the configured clock, and
 * may be manipulated in tests.
 */
final class TestingRateLimitStorage implements RateLimitStorage
{
    /** @var array<string, RateLimitState> */
    private array $states = [];

    public function __construct(
        private readonly Clock $clock,
    ) {}

    public function find(string $key): ?RateLimitState
    {
        $state = $this->states[$key] ?? null;

        if ($state === null) {
            return null;
        }

        if ($state->resetsAtInSeconds <= $this->clock->seconds()) {
            unset($this->states[$key]);

            return null;
        }

        return $state;
    }

    public function increment(string $key, Duration $window, int $by = 1): RateLimitState
    {
        return $this->states[$key] = $this->find($key)?->incrementedBy($by) ?? RateLimitState::opening($this->clock, $window, hits: $by);
    }

    public function remove(string $key): void
    {
        unset($this->states[$key]);
    }
}
