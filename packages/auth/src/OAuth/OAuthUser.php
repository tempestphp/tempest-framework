<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Mapper\MapFrom;

final readonly class OAuthUser
{
    public function __construct(
        /**
         * The unique identifier for the user from the OAuth provider.
         */
        #[MapFrom('id', 'Id', 'uid', 'uuid')]
        public string $id,

        /**
         * The user's email address.
         */
        #[MapFrom('email', 'emailAddress', 'email_address')]
        public ?string $email = null,

        /**
         * The user's name.
         */
        #[MapFrom('name', 'displayName', 'display_name', 'fullName', 'full_name')]
        public ?string $name = null,

        /**
         * The user's nickname/username.
         */
        #[MapFrom('nickname', 'username', 'login', 'handle')]
        public ?string $nickname = null,

        /**
         * The user's avatar URL.
         */
        #[MapFrom('avatar', 'avatar_url', 'avatarUrl', 'picture', 'profileImage', 'profile_image')]
        public ?string $avatar = null,

        /**
         * The OAuth provider name.
         */
        public string $provider = 'default',

        /**
         * Raw user data from the OAuth provider.
         *
         * @var array<string, mixed>
         */
        public array $raw = [],
    ) {}
}
