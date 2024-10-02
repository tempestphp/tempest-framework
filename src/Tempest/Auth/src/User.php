<?php

declare(strict_types=1);

namespace Tempest\Auth;

use BackedEnum;
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

    public function grantPermission(string|UnitEnum $permission): self
    {
        $permission = match(true) {
            is_string($permission) => $permission,
            $permission instanceof BackedEnum => $permission->value,
            $permission instanceof UnitEnum => $permission->name,
        };

        (new UserPermission(
            user: $this,
            permission: new Permission($permission)
        ))->save();

        return $this->load('userPermissions.permission');
    }

    public function revokePermission(string|UnitEnum $permission): self
    {
        $this->getPermission($permission)?->delete();

        return $this->load('userPermissions.permission');
    }

    public function hasPermission(UnitEnum|string $permission): bool
    {
        return $this->getPermission($permission) !== null;
    }

    public function getPermission(UnitEnum|string $permission): ?UserPermission
    {
        return arr($this->userPermissions)
            ->first(fn (UserPermission $userPermission) => $userPermission->permission->matches($permission));
    }
}
