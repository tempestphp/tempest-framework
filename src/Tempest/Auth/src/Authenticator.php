<?php

declare(strict_types=1);

namespace Tempest\Auth;

interface Authenticator
{
    public function login(CanAuthenticate $user): void;

    public function logout(): void;

    public function currentUser(): ?CanAuthenticate;
}
