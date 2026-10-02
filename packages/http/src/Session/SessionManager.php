<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

interface SessionManager
{
    /**
     * Retrieves or creates a session based on its identifier.
     */
    public function getOrCreate(SessionId $id): Session;

    /**
     * Saves the session data to the server.
     */
    public function save(Session $session): void;

    /**
     * Removes the session from the server.
     */
    public function delete(Session $session): void;

    /**
     * Assigns a new identifier to the session, destroying the session it replaces
     * and sending the new identifier to the client. Session data is carried over.
     *
     * This protects against session fixation, and should be done whenever the session
     * changes privilege level - such as authentication or a password change.
     *
     * @see https://cheatsheetseries.owasp.org/cheatsheets/Session_Management_Cheat_Sheet.html
     * @see https://owasp.org/www-community/attacks/Session_fixation
     */
    public function regenerate(Session $session): void;

    /**
     * Determines whether the session is still valid.
     */
    public function isValid(Session $session): bool;

    /**
     * Removes all expired sessions from the server.
     */
    public function deleteExpiredSessions(): void;
}
