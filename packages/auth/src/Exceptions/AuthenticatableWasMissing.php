<?php

declare(strict_types=1);

namespace Tempest\Auth\Exceptions;

use Exception;

final class AuthenticatableWasMissing extends Exception implements AuthenticationException
{
    public function __construct()
    {
        parent::__construct('There is no currently authenticated model.');
    }
}
