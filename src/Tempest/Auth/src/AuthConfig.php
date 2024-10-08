<?php

declare(strict_types=1);

namespace Tempest\Auth;

final class AuthConfig
{
    public function __construct(
        /** @var class-string<\Tempest\Auth\Authenticator> */
        public string $authenticatorClass = SessionAuthenticator::class,

        /** @var class-string<\Tempest\Database\DatabaseModel> */
        public string $userModelClass = User::class,
    ) {
    }
}
