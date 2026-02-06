<?php

declare(strict_types=1);

namespace Tempest\Router;

/**
 * Defines how to resolve the client identifier for rate limiting.
 */
enum RateLimitBy: string
{
    /** Use the client's IP address. */
    case IP = 'ip';

    /** Use the authenticated user's ID. */
    case USER = 'user';

    /** Use the session ID. */
    case SESSION = 'session';
}
