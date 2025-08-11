<?php

namespace Tempest\Auth\Installer;

use Tempest\Auth\Authentication\CanAuthenticate;
use Tempest\Database\Hashed;
use Tempest\Database\PrimaryKey;
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final class StubUserModel implements CanAuthenticate
{
    public PrimaryKey $id;

    public function __construct(
        public string $email,
        #[Hashed]
        public ?string $password,
    ) {}
}
