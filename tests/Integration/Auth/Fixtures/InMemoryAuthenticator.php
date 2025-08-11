<?php

namespace Tests\Tempest\Integration\Auth\Fixtures;

use Tempest\Auth\Authentication\Authenticator;
use Tempest\Auth\Authentication\CanAuthenticate;

final class InMemoryAuthenticator implements Authenticator
{
    private ?CanAuthenticate $authenticatable = null;

    public function authenticate(CanAuthenticate $authenticatable): void
    {
        $this->authenticatable = $authenticatable;
    }

    public function deauthenticate(): void
    {
        $this->authenticatable = null;
    }

    public function current(): ?CanAuthenticate
    {
        return $this->authenticatable;
    }
}
