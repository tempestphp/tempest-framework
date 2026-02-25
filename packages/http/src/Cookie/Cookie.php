<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

use InvalidArgumentException;
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
     * @param bool $secure When `true`, this cookie is only transmitted over secure connections.
     * @param bool $httpOnly When `true`, this cookie will not be accessible using JavaScript.
     * @param null|SameSite $sameSite See {@see \Tempest\Http\Cookie\SameSite}.
     */
    public function __construct(
        public string $key,
        public ?string $value = null,
        public DateTimeInterface|int|null $expiresAt = null,
        public ?int $maxAge = null,
        public ?string $domain = null,
        public ?string $path = '/',
        public bool $secure = true,
        public bool $httpOnly = false,
        public ?SameSite $sameSite = SameSite::LAX,
    ) {}

    public function withValue(string $value): self
    {
        $clone = clone $this;
        $clone->value = $value;

        return $clone;
    }

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

    /**
     * Creates acookie from the `Set-Cookie` header.
     */
    public static function createFromString(string $string): self
    {
        if (! ($attributes = preg_split('/\s*;\s*/', $string, -1, PREG_SPLIT_NO_EMPTY))) {
            throw new InvalidArgumentException(sprintf('The raw value of the `Set Cookie` header `%s` could not be parsed.', $string));
        }

        $nameAndValue = explode('=', array_shift($attributes), 2);
        $cookie = ['name' => $nameAndValue[0], 'value' => isset($nameAndValue[1]) ? urldecode($nameAndValue[1]) : ''];

        while ($attribute = array_shift($attributes)) {
            $attribute = explode('=', $attribute, 2);
            $attributeName = strtolower($attribute[0]);
            $attributeValue = $attribute[1] ?? null;

            if (in_array($attributeName, ['expires', 'domain', 'path', 'samesite'], true)) {
                $cookie[$attributeName] = $attributeValue;
                continue;
            }

            if (in_array($attributeName, ['secure', 'httponly'], true)) {
                $cookie[$attributeName] = true;
                continue;
            }

            if ($attributeName === 'max-age') {
                $cookie['max-age'] = (int) $attributeValue;
                $cookie['expires'] = time() + (int) $attributeValue;
            }
        }

        return new Cookie(
            key: $cookie['name'],
            value: $cookie['value'] ?? null,
            expiresAt: isset($cookie['expires']) ? (int) $cookie['expires'] : null,
            maxAge: isset($cookie['max-age']) ? (int) $cookie['max-age'] : null,
            domain: $cookie['domain'] ?? null,
            path: $cookie['path'] ?? '/',
            secure: isset($cookie['secure']) && $cookie['secure'] === true,
            httpOnly: isset($cookie['httponly']) && $cookie['httponly'] === true,
            sameSite: isset($cookie['samesite']) ? SameSite::from($cookie['samesite']) : null,
        );
    }
}
