<?php

namespace Tempest\Generation\Tests\TypeScript\Fixtures\Metadata;

enum RoleWithMetadata: string
{
    #[Description('An administrator')]
    case ADMIN = 'admin';

    #[Description('A normal user')]
    case USER = 'user';

    #[Description('A guest')]
    case GUEST = 'guest';
}
