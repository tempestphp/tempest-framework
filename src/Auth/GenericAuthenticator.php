<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Http\Session\Session;

abstract class GenericAuthenticator implements Authenticator
{
    public const string SESSION_USER_KEY = 'tempest_session_user';

    public function __construct(protected Session $session)
    {
    }

    public function logout(): void
    {
        $this->session->remove(self::SESSION_USER_KEY);
        $this->session->destroy();
    }

    protected function createSession(Identifiable $identifiable): void
    {
        $this->session->set(self::SESSION_USER_KEY, [
            'source' => $identifiable->source(),
            'identifier_field' => $identifiable->identifier(),
            'identifier_value' => $identifiable->identifierValue(),
        ]);
    }
}
