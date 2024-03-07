<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

use DateTimeImmutable;

final class CookieManager
{
    private function __construct(
        /** @var \Tempest\Http\Cookie\Cookie[] $cookies */
        public array $cookies = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $cookieManager = new self();

        foreach ($_COOKIE as $key => $value) {
            $cookieManager->add(new Cookie($key, $value));
        }

        return $cookieManager;
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
        $this->cookies[$cookie->key] = $cookie;
    }

    public function remove(string $key): void
    {
        $cookie = new Cookie($key, '', -1);

        $this->add($cookie);
    }
}
