<?php

declare(strict_types=1);

namespace Tempest\Auth;

use Tempest\Http\Session\Session;

final readonly class SessionAuthenticator implements Authenticator
{
    private const string SESSION_USER_KEY = 'tempest_session_user';

    public function __construct(
        protected Session $session,
    ) {
    }

    public function login(Identifiable $identifiable): void
    {
        $this->session->set(self::SESSION_USER_KEY, [
            'identifier_field' => $identifiable->identifierField(),
            'identifier_value' => $identifiable->identifierValue(),
        ]);
    }

    public function logout(): void
    {
        $this->session->remove(self::SESSION_USER_KEY);
        $this->session->destroy();
    }

    public function getSessionInfo(): array|null
    {
        return $this->session->get(self::SESSION_USER_KEY);
    }
}
