<?php

declare(strict_types=1);

namespace Tempest\Auth;

interface CredentialsResolver
{
    public function resolve(AuthenticationCall $call): Identifiable;
}
