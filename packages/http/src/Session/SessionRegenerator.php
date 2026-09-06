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
     * Assigns a new ID to the current session, carrying over data by default.
     */
    public function regenerate(bool $preserveData = true): void
    {
        // Destroy the old session to prevent parallel active sessions.
        $this->sessionManager->delete($this->session);

        $this->session->replaceId(
            id: $this->sessionIdResolver->regenerate(),
            preserveData: $preserveData,
        );

        $this->sessionManager->save($this->session);
    }
}
