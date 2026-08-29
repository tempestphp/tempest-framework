<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

use Tempest\DateTime\Duration;
use Tempest\RateLimit\Storage\RateLimitState;

interface RateLimitStorage
{
    /**
     * Returns the state of the current window for the specified key, or `null` when no window is open.
     */
    public function find(string $key): ?RateLimitState;

    /**
     * Records attempts against the specified key, opening a window of the specified duration when none is open.
     */
    public function increment(string $key, Duration $window, int $by = 1): RateLimitState;

    /**
     * Closes the window for the specified key, discarding the attempts made within it.
     */
    public function remove(string $key): void;
}
