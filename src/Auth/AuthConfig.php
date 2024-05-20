<?php

declare(strict_types=1);

namespace Tempest\Auth;

final class AuthConfig
{
    public function __construct(
        /** @var class-string<Authenticator> */
        public string $authenticator = DatabaseAuthenticator::class,
        /** @var class-string|null */
        public ?string $authenticable = null,
    ) {
    }
}
