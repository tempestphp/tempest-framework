<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

final class CookieConfig
{
    public function __construct(
        /**
         * Whether to discard cookies that cannot be decrypted.
         * What this means: any cookies not encrypted by your application (or not whitelisted) that
         * arrive with a request, will prompt tempest to request the browser to forget these cookies.
         * Cookies sent unencrypted and not whitelisted will also not be available in the request object.
         */
        public bool $discardUnencryptedCookies = true,

        /**
         * List of cookies that will not be decrypted by tempest, be available in the request object.
         * Outgoing whitelisted cookies will be sent to the browser in plaintext.
         */
        public array $plaintextCookies = [],
    ) {}
}
