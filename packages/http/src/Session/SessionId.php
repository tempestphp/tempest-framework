<?php

declare(strict_types=1);

namespace Tempest\Http\Session;

use Stringable;

/**
 * Represents a unique identifier for a session.
 */
final readonly class SessionId implements Stringable
{
    /**
     * Restricts session IDs to safe characters to prevent path traversal
     * when used as filenames (e.g., by {@see Managers\FileSessionManager}).
     */
    private const string PATTERN = '/^[A-Za-z0-9_-]{1,128}\z/';

    public function __construct(
        private string $id,
    ) {
        if (preg_match(self::PATTERN, $id) !== 1) {
            throw new InvalidSessionId($id);
        }
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
