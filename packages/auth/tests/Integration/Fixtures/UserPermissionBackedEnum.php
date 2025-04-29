<?php

declare(strict_types=1);

namespace Tempest\Auth\Tests\Integration\Fixtures;

enum UserPermissionBackedEnum: string
{
    case ADMIN = 'admin';
    case GUEST = 'guest';
}
