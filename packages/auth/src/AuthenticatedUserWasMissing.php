<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Exception;

final class AuthenticatedUserWasMissing extends Exception
{
    public function __construct()
    {
        parent::__construct('Tried to request the current user, but no one is logged in');
    }
}
