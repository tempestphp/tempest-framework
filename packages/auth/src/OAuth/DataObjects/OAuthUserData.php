<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth\DataObjects;

use Tempest\Mapper\MapFrom;

use function Tempest\map;

final readonly class OAuthUserData
{
    public function __construct(
        #[MapFrom('ID', 'Id', 'sub', 'user_id')]
        public int|string|null $id = null,

        #[MapFrom('nickname', 'nick_name', 'login', 'username', 'user_name', 'preferred_username', 'given_name')]
        public ?string $nickname = null,

        #[MapFrom('name', 'full_name', 'fullName', 'display_name', 'displayName', 'family_name')]
        public ?string $name = null,

        public ?string $email = null,

        #[MapFrom('avatar', 'avatar_url', 'picture', 'profile_image_url')]
        public ?string $avatar = null,
        public array $rawData = [],
    ) {}

    public static function from(array $rawData): self
    {
        return map([
            'rawData' => $rawData,
            ...$rawData,
        ])
            ->to(self::class);
    }
}
