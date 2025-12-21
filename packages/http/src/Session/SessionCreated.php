<?php

namespace Tempest\Http\Session;

/**
 * Dispatched when the session manager creates a session.
 */
final class SessionCreated
{
    public function __construct(
        private(set) Session $session,
    ) {}
}
