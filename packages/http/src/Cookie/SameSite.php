<?php

declare(strict_types=1);

namespace Tempest\Http\Cookie;

/**
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Set-Cookie#samesitesamesite-value
 */
enum SameSite: string
{
    /**
     * Send the cookie only for requests originating from the same site that set the cookie.
     */
    case STRICT = 'Strict';

    /**
     * Send the cookie for requests originating from the same site that set the cookie, and for cross-site requests that meet both of the following criteria:
     * - The request is a top-level navigation: this essentially means that the request causes the URL shown in the browser's address bar to change.
     * - The request uses a safe method: in particular, this excludes `POST`, `PUT`, and `DELETE`.
     */
    case LAX = 'Lax';

    /**
     * Send the cookie with both cross-site and same-site requests. The `Secure` attribute must also be set when using this value.
     */
    case NONE = 'None';
}
