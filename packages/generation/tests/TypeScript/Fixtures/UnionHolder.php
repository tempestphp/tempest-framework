<?php

namespace Tempest\Generation\Tests\TypeScript\Fixtures;

use Tempest\Generation\Tests\TypeScript\Fixtures\Security\Permission;
use Tempest\Generation\Tests\TypeScript\Fixtures\Security\Role;

final class UnionHolder
{
    public function __construct(
        public Role|Permission $value,
    ) {}
}
