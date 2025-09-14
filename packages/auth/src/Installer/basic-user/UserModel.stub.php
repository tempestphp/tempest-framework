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
        public ?string $password,
    ) {}
}
