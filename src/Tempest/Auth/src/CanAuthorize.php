<?php

declare(strict_types=1);

namespace Tempest\Auth;

use UnitEnum;

interface CanAuthorize
{
    public function grantPermission(string|UnitEnum $permission): self;

    public function hasPermission(string|UnitEnum $permission): bool;
}
