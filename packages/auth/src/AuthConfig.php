<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Auth\Install\User;

final class AuthConfig
{
    public function __construct(
        /** @var class-string<\Tempest\Auth\Authenticator> */
        public string $authenticatorClass = SessionAuthenticator::class,

        /** @var class-string */
        public string $userModelClass = User::class,
    ) {}
}
