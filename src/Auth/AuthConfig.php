<?php

declare(strict_types=1);

namespace Tempest\Auth;

final class AuthConfig
{
    public function __construct(
        /** @var class-string<Identifiable>|null */
        public ?string $identifiable = null,

        /** @var array<string,class-string<Authenticator>> */
        public array $authenticators = [
            'database' => DatabaseAuthenticator::class,
        ],

        /** @var array<string,class-string<CredentialsResolver>> */
        public array $credentials = [
            'database' => DatabaseCredentials::class,
        ],

        /** @var array<string,string> */
        public array $databaseSource = [
            'source' => 'users',
            'identifier' => 'email',
            'password' => 'password',
        ],
    ) {
    }
}
