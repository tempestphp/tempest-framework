<?php

namespace Tests\Tempest\Integration\Auth\Fixtures;

use Tempest\Auth\Authentication\Authenticatable;
use Tempest\Auth\Authentication\Authenticator;

final class InMemoryAuthenticator implements Authenticator
{
    private ?Authenticatable $authenticatable = null;

    public function authenticate(Authenticatable $authenticatable): void
    {
        $this->authenticatable = $authenticatable;
    }

    public function deauthenticate(): void
    {
        $this->authenticatable = null;
    }

    public function current(): ?Authenticatable
    {
        return $this->authenticatable;
    }
}
