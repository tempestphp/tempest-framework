<?php

namespace Tempest\Generation\Tests\TypeScript\Fixtures\Security;

final class Permission
{
    public function __construct(
        public readonly string $name,
    ) {}
}
