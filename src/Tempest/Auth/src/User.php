<?php

declare(strict_types=1);

namespace Tempest\Auth;

use SensitiveParameter;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;

final class User implements DatabaseModel, CanAuthenticate
{
    use IsDatabaseModel;

    public string $password;

    public function __construct(
        public string $name,
        public string $email,
    ) {
    }

    /**
     * @param string $password The raw password, which will be encrypted as soon as it is set
     */
    public function setPassword(#[SensitiveParameter] string $password): self
    {
        $this->password = password_hash($password, PASSWORD_BCRYPT);

        return $this;
    }
}
