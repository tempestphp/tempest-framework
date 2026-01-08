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
     * Determines whether the session is still valid.
     */
    public function isValid(Session $session): bool;

    /**
     * Removes all expired sessions from the server.
     */
    public function deleteExpiredSessions(): void;
}
