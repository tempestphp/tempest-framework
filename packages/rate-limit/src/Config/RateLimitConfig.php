<?php

declare(strict_types=1);

namespace Tempest\RateLimit\Config;

use Tempest\Container\Container;
use Tempest\RateLimit\Http\RateLimitKeyResolver;
use Tempest\RateLimit\RateLimitStorage;

interface RateLimitConfig
{
    /**
     * Prefix used for the keys under which rate limit windows are stored.
     */
    public string $keyPrefix { get; }

    /**
     * Whether HTTP responses include `X-RateLimit-*` headers. These headers are per-client and must
     * not be cached by a shared proxy.
     */
    public bool $includeHeaders { get; }

    /**
     * @var class-string<RateLimitKeyResolver>
     */
    public string $keyResolverClass { get; }

    /**
     * Returns the key a rate limit's window is stored under. Keys are hashed, since a limit may be
     * scoped to arbitrary input that the store would not accept as a key.
     */
    public function storageKey(string $key): string;

    /**
     * Creates the storage in which rate limit windows are kept.
     */
    public function createStorage(Container $container): RateLimitStorage;
}
