<?php

declare(strict_types=1);

namespace Tempest\Router;

/**
 * Represents the Sec-Fetch-Mode header value.
 *
 * This header indicates the mode of the request.
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Sec-Fetch-Mode
 */
enum SecFetchMode: string
{
    /**
     * The request is for navigation between HTML pages.
     */
    case NAVIGATE = 'navigate';

    /**
     * The request is for CORS-enabled requests.
     */
    case CORS = 'cors';

    /**
     * The request is for no-CORS requests.
     */
    case NO_CORS = 'no-cors';

    /**
     * The request is for same-origin requests.
     */
    case SAME_ORIGIN = 'same-origin';

    /**
     * The request is for WebSocket connections.
     */
    case WEBSOCKET = 'websocket';
}
