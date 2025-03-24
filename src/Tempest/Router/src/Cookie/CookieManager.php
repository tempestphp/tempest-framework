<?php

declare(strict_types=1);

namespace Tempest\Router\Cookie;

use DateTimeImmutable;
use Tempest\Clock\Clock;

final class CookieManager
{
    /** @var \Tempest\Router\Cookie\Cookie[] */
    private array $cookies = [];

    public function __construct(
        private Clock $clock,
    ) {}

    /** @return  \Tempest\Router\Cookie\Cookie[] */
    public function all(): array
    {
        return $this->cookies;
    }

    public function get(string $key): ?Cookie
    {
        return $this->cookies[$key] ?? null;
    }

    public function set(
        string $key,
        string $value,
        DateTimeImmutable|int|null $expiresAt = null,
    ): Cookie {
        $cookie = $this->get($key) ?? new Cookie(key: $key);

        $cookie->value = $value;
        $cookie->expiresAt = $expiresAt ?? $cookie->expiresAt;

        $this->add($cookie);

        return $cookie;
    }

    public function add(Cookie $cookie): void
    {
        if ($cookie->expiresAt !== null) {
            $maxAge = $cookie->getExpiresAtTime() - $this->clock->time();

            $cookie->maxAge = max($maxAge, 0);
        }

        $this->cookies[$cookie->key] = $cookie;
    }

    public function remove(string $key): void
    {
        $cookie = new Cookie($key, '', -1);

        $this->add($cookie);
    }
}
