<?php

declare(strict_types=1);

namespace Tempest\Auth;

use SensitiveParameter;
use Tempest\Database\DatabaseModel;
use Tempest\Database\IsDatabaseModel;
use function Tempest\Support\arr;
use UnitEnum;

final class User implements DatabaseModel, CanAuthenticate, CanAuthorize
{
    use IsDatabaseModel;

    public string $password;

    public function __construct(
        public string $name,
        public string $email,
        /** @var \Tempest\Auth\UserPermission[] $userPermissions */
        public array $userPermissions = [],
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

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function hasPermission(UnitEnum|string $permission): bool
    {
        return arr($this->permissions)->contains($permission);
    }
}
