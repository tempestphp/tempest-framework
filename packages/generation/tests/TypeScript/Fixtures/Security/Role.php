<?php

namespace Tempest\Generation\Tests\TypeScript\Fixtures\Security;

final class Role
{
    public function __construct(
        public readonly string $name,
        /** @var \Tempest\Generation\Tests\TypeScript\Fixtures\Security\Permission[] */
        public readonly array $permissions,
    ) {}
}
