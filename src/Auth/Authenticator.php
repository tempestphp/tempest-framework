<?php

declare(strict_types=1);

namespace Tempest\Auth;

interface Authenticator
{
    public function login(Identifiable $identifiable): void;

    public function logout(): void;

    public function user(): Identifiable|array|null;
}
