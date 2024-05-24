<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Http\Session\Session;

trait AuthenticatorWithSession
{
    protected Session $session;

    public const string SESSION_USER_KEY = 'tempest_session_user';

    protected function createSession(Identifiable $identifiable): void
    {
        $this->session->set(self::SESSION_USER_KEY, [
            'identifier_field' => $identifiable->identifierField(),
            'identifier_value' => $identifiable->identifierValue(),
        ]);
    }

    protected function destroySession(): void
    {
        $this->session->remove(self::SESSION_USER_KEY);
        $this->session->destroy();
    }
}
