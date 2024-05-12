<?php

declare(strict_types=1);

namespace Tempest\Auth\Authenticators;

use Tempest\Auth\Contracts\Authenticator;
use Tempest\Http\Session\Session;

abstract class GenericAuthenticator implements Authenticator
{
    public function __construct(protected Session $session)
    {
    }
}
