<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

use Stringable;
use Tempest\DateTime\DateTimeInterface;

/**
 * @see https://github.com/httpsoft/http-cookie/blob/master/src/Cookie.php
 */
final class Cookie implements Stringable
{
    /**
     * @param null|int $maxAge The maximum age of the cookie in seconds. This is an alternative to the expiration date. If set, the cookie will expire after the specified number of seconds.
     * @param null|string $domain This specifies the domain for which the cookie is valid. If set, the cookie will be sent to this domain and its subdomains. If `⁠null`, it defaults to the current domain.
     * @param null|string $path The URL path that must exist in the requested URL for the cookie to be sent. If set, the cookie will only be sent for requests to this path and its subdirectories.
     * @param null|bool $secure When `true`, this cookie is only transmitted over secure connections.
     * @param null|bool $httpOnly When `true`, this cookie will not be accessible using JavaScript.
     * @param null|SameSite $sameSite See {@see \Tempest\Http\Cookie\SameSite}.
     */
    public function __construct(
        public string $key,
        public ?string $value = null,
        public DateTimeInterface|int|null $expiresAt = null,
        public ?int $maxAge = null,
        public ?string $domain = null,
        public ?string $path = '/',
        public bool $secure = false,
        public bool $httpOnly = false,
        public ?SameSite $sameSite = null,
    ) {}

    public function __toString(): string
    {
        $parts = [
            $this->key . '=' . rawurlencode($this->value ?? ''),
        ];

        if ($expiresAt = $this->getExpiresAtTime()) {
            $parts[] = 'Expires=' . gmdate('D, d-M-Y H:i:s T', $expiresAt);
            $parts[] = 'Max-Age=' . $this->maxAge;
        }

        if ($this->domain !== null) {
            $parts[] = 'Domain=' . $this->domain;
        }

        if ($this->path !== null) {
            $parts[] = 'Path=' . $this->path;
        }

        if ($this->secure === true) {
            $parts[] = 'Secure';
        }

        if ($this->httpOnly === true) {
            $parts[] = 'HttpOnly';
        }

        if ($this->sameSite !== null) {
            $parts[] = 'SameSite=' . $this->sameSite->value;
        }

        return implode('; ', $parts);
    }

    public function getExpiresAtTime(): ?int
    {
        if ($this->expiresAt instanceof DateTimeInterface) {
            return $this->expiresAt->getTimestamp()->getSeconds();
        }

        if (is_int($this->expiresAt)) {
            return $this->expiresAt;
        }

        return null;
    }
}
