<?php

namespace Tempest\Auth\Installer;

use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Database\Hashed;
use Tempest\Database\PrimaryKey;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class UserModel implements Authenticatable
{
    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed]
        #[\SensitiveParameter]
        public ?string $password,
        public ?string $name,
        public ?string $nickname,
        public ?string $avatar,
        public ?string $oauth_id,
        public ?array $oauth_raw,
        public ?string $oauth_provider,
    ) {}
}
