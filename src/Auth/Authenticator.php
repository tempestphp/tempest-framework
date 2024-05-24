<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Auth\Exceptions\MissingIdentifiableException;

interface Authenticator
{
    public function login(Identifiable $identifiable): void;

    public function logout(): void;

    /**
     * @throws MissingIdentifiableException
     */
    public function user(): Identifiable|null;
}
