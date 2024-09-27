<?php

declare(strict_types=1);

namespace Tempest\Auth;

interface Authenticator
{
    public function login(User $user): void;

    public function logout(): void;

    public function currentUser(): ?User;
}
