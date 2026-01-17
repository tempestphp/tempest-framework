<?php

namespace Tempest\Generation\Tests\TypeScript\Fixtures;

use DateTimeInterface;

final class User
{
    public function __construct(
        public string $full_name,
        public string $email,
        public int $age,
        public DateTimeInterface $created_at,
        /** @var \Tempest\Generation\Tests\TypeScript\Fixtures\Security\Role[] */
        public array $roles,
        public Settings $settings,
    ) {}
}
