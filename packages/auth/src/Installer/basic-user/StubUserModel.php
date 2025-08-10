<?php

namespace Tempest\Auth\Installer;

use Tempest\Database\PrimaryKey;

final class StubUserModel
{
    public PrimaryKey $id;

    public function __construct(
        public string $email,
        public string $password,
    ) {}
}
