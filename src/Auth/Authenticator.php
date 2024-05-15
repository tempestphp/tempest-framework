<?php

declare(strict_types=1);

namespace Tempest\Auth;

interface Authenticator
{
    public function login(Identifiable $identifiable): void;

    public function logout(): void;

    /**
     * @return Identifiable|null
     */
    public function user();
}
