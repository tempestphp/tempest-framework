<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

/**
 * Regenerates the session identifier to prevent session fixation attacks.
 *
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html
 * @see https://owasp.org/www-community/attacks/Session_fixation
 */
final readonly class SessionRegenerator
{
    public function __construct(
        private SessionManager $sessionManager,
        private Session $session,
        private SessionIdResolver $sessionIdResolver,
    ) {}

    /**
     * Assigns a new identifier to the current session, destroying the session it replaces.
     *
     * Session data is carried over. Callers are responsible for persisting the session
     * afterwards, and for clearing its data first if it should not survive.
     */
    public function regenerate(): void
    {
        $this->sessionManager->delete($this->session);

        $this->session->replaceId($this->sessionIdResolver->issueNewId());
    }
}
