<?php

namespace Tempest\Auth\Authentication;

use Tempest\Container\Resettable;

final readonly class SessionAuthenticatorReset implements Resettable
{
    public function __construct(
        private SessionAuthenticator $sessionAuthenticator,
    ) {}

    public function reset(): void
    {
        $this->sessionAuthenticator->clearCurrent();
    }
}
