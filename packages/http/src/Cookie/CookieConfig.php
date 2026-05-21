<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

final class CookieConfig
{
    public function __construct(
        public bool $discardUnencryptedCookies = true,
        public array $plaintextCookies = [],
    ) {}
}
