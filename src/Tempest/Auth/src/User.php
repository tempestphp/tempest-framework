<?php

namespace Tempest\Auth;

use SensitiveParameter;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class User implements DatabaseModel
{
    use IsDatabaseModel;

    public function __construct(
        public string $name,
        public string $email,
        #[SensitiveParameter]
        public string $password,
    ) {}
}