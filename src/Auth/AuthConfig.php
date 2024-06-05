<?php

declare(strict_types=1);

namespace Tempest\Auth;

final class AuthConfig
{
    public function __construct(
        /** @var class-string<Identifiable>|null */
        public ?string $identifiable = null,

        /** @var class-string<Authenticator> */
        public string $authenticator = SessionAuthenticator::class,

        /** @var array<string,class-string<IdentifierResolver>> */
        public array $identifiers = [
            'database' => DatabaseIdentifier::class,
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
