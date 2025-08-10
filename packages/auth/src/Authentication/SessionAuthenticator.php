<?php

declare(strict_types=1);

namespace Tempest\Auth\Authentication;

use Tempest\Auth\AuthConfig;
use Tempest\Http\Session\Session;

final readonly class SessionAuthenticator implements Authenticator
{
    public const string AUTHENTICATABLE_KEY = '#authenticatable';

    public function __construct(
        private AuthConfig $authConfig,
        private Session $session,
        private AuthenticatableResolver $authenticatableResolver,
    ) {}

    public function authenticate(CanAuthenticate $authenticatable): void
    {
        $this->session->set(
            key: self::AUTHENTICATABLE_KEY,
            value: $this->authenticatableResolver->resolveId($authenticatable),
        );
    }

    public function deauthenticate(): void
    {
        $this->session->remove(self::AUTHENTICATABLE_KEY);
        $this->session->destroy();
    }

    public function current(): ?CanAuthenticate
    {
        $id = $this->session->get(self::AUTHENTICATABLE_KEY);

        if (! $id) {
            return null;
        }

        return $this->authenticatableResolver->resolve($id);
    }
}
