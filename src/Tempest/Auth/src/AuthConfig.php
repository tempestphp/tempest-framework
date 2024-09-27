<?php

namespace Tempest\Auth;

final class AuthConfig
{
    public function __construct(
        /** @var class-string<\Tempest\Auth\Authenticator> */
        public string $authenticatorClass = SessionAuthenticator::class,
    ) {}
}