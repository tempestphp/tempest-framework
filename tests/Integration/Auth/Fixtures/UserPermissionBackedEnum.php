<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Auth\Fixtures;

enum UserPermissionBackedEnum: string
{
    case ADMIN = 'admin';
    case GUEST = 'guest';
}
