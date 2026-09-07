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
     * Assigns a new ID to the current session, carrying over data.
     */
    public function regenerate(): void
    {
        $this->sessionManager->delete($this->session);

        $this->session->replaceId($this->sessionIdResolver->issueNewId());

        $this->sessionManager->save($this->session);
    }

    /**
     * Assigns a new ID to the current session, discarding all data.
     */
    public function invalidate(): void
    {
        $this->sessionManager->delete($this->session);

        $this->session->replaceId($this->sessionIdResolver->issueNewId());
        $this->session->clear();

        $this->sessionManager->save($this->session);
    }
}
