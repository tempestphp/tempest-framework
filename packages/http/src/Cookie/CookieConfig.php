<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

final class CookieConfig
{
    /**
     *
     * @param bool $discardUnencryptedCookies Whether to discard cookies that cannot be decrypted.
     * @param string[] $plaintextCookies List of cookies keys that will not be decrypted by Tempest. Outgoing whitelisted cookies will be sent to the browser in plaintext.
     */
    public function __construct(
        public bool $discardUnencryptedCookies = true,
        public array $plaintextCookies = [],
    ) {}
}
