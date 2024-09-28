<?php

declare(strict_types=1);

namespace Tempest\Auth;

use UnitEnum;

interface CanAuthorize
{
    /** @return array<array-key, string|UnitEnum> */
    public function getPermissions(): array;

    public function hasPermission(string|UnitEnum $permission): bool;
}
