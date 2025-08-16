<?php

declare(strict_types=1);

namespace Tempest\Auth\Authentication;

interface Authenticator
{
    /**
     * Authenticates the given model.
     */
    public function authenticate(CanAuthenticate $authenticatable): void;

    /**
     * Deauthenticates the currently authenticated model.
     */
    public function deauthenticate(): void;

    /**
     * Retrieves the currently authenticated model.
     */
    public function current(): ?CanAuthenticate;
}
