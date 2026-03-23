<?php

namespace Tempest\Http\Session;

enum CleanupStrategy
{
    /**
     * Expired sessions will be cleaned up after every request. This may cause performance issues on high-traffic applications, but ensures that expired sessions are removed as soon as possible.
     */
    case EVERY_REQUEST;

    /**
     * Expired sessions will be cleaned up after a random request. This is the default strategy, and provides a good balance between performance and cleanup frequency.
     */
    case RANDOM_REQUESTS;

    /**
     * Expired sessions will only be cleaned up when the `session:clean` command is executed. This is the most performant strategy, but requires that the command is scheduled to run regularly (e.g. every minute).
     */
    case DISABLED;
}
