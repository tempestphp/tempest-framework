<?php

declare(strict_types=1);

namespace Tempest\Auth\Contracts;

interface Authenticator
{
    public function login(Authenticable $authenticable): void;

    public function logout(): void;

    public function user(): ?Authenticable;
}
