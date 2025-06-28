<?php

declare(strict_types=1);

namespace Tempest\Icon;

use Tempest\DateTime\Duration;

final class IconConfig
{
    /**
     * @param string $iconifyApiUrl URL of the Iconify API. This allows you to switch to a local or self-hosted Iconify API.
     * @param Duration $retryAfter Specify the duration to wait before retrying a request to the Iconify API after a previous one failed.
     * @param null|Duration $expiresAfter The number of seconds to cache the icon SVG files. If null, the icons will be cached indefinitely.
     */
    public function __construct(
        public string $iconifyApiUrl,
        public Duration $retryAfter,
        public ?Duration $expiresAfter = null,
    ) {}
}
