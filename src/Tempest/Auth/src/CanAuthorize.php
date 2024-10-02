<?php

declare(strict_types=1);

namespace Tempest\Auth;

use UnitEnum;

interface CanAuthorize
{
    public function hasPermission(string|UnitEnum $permission): bool;
}
