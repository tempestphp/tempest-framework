<?php

declare(strict_types=1);

namespace Tempest\RateLimit;

use Exception;

/**
 * Base class for exceptions thrown by the rate limit component.
 */
abstract class RateLimitException extends Exception {}
