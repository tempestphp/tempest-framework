<?php

namespace Tempest\Auth;

use Tempest\Http\Session\Session;

final readonly class SessionAuthenticator implements Authenticator
{
    private const string USER_KEY = 'tempest_session_user';

    public function __construct(
        private Session $session,
    ) {}

    public function login(User $user): void
    {
        $this->session->set(self::USER_KEY, $user->id);
    }

    public function logout(): void
    {
        $this->session->remove(self::USER_KEY);
        $this->session->destroy();
    }

    public function currentUser(): ?User
    {
        $id = $this->session->get(self::USER_KEY);

        if (! $id) {
            return null;
        }

        return User::query()->find($id);
    }
}