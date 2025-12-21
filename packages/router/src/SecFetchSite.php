<?php

declare(strict_types=1);

namespace Tempest\Router;

/**
 * Represents the `Sec-Fetch-Site` header value.
 *
 * This header indicates the relationship between a request initiator's origin
 * and the origin of the resource being requested.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Site
 */
enum SecFetchSite: string
{
    /**
     * The request was initiated from the same origin.
     */
    case SAME_ORIGIN = 'same-origin';

    /**
     * The request was initiated from a same-site but cross-origin context.
     */
    case SAME_SITE = 'same-site';

    /**
     * The request was initiated from a cross-site context.
     */
    case CROSS_SITE = 'cross-site';

    /**
     * The request was initiated in a user-initiated way (e.g., entering a URL in the address bar).
     */
    case NONE = 'none';
}
