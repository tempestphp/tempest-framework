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
    public function __construct(
        public string $key,
        public string $value = '',
        public DateTimeInterface|int|null $expiresAt = null,
        public ?int $maxAge = null,
        public ?string $domain = null,
        public ?string $path = null,
        public ?bool $secure = null,
        public ?bool $httpOnly = null,
        public ?SameSite $sameSite = null,
    ) {}

    public function __toString(): string
    {
        $parts = [
            $this->key . '=' . rawurlencode($this->value),
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
