<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Auth\Authenticators\DatabaseAuthenticator;
use Tempest\Auth\Contracts\Authenticator;

final class AuthConfig
{
    public function __construct(
        /** @var class-string<Authenticator> */
        public string $authenticator = DatabaseAuthenticator::class,
    ) {
    }
}
