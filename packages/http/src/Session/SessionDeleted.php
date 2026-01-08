<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

/**
 * Dispatched when the session manager deletes a session.
 */
final readonly class SessionDeleted
{
    public function __construct(
        public SessionId $id,
    ) {}
}
