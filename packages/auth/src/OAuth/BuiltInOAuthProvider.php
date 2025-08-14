<?php

declare(strict_types=1);

namespace Tempest\Auth\OAuth;

use Tempest\Support\IsEnumHelper;

enum BuiltInOAuthProvider: string
{
    use IsEnumHelper;

    case GITHUB = 'github';
    case GOOGLE = 'google';
}
