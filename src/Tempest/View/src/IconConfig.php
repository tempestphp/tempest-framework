<?php

declare(strict_types=1);

namespace Tempest\View;

final class IconConfig
{
    public function __construct(
        /**
         * The number of seconds to cache the icon SVG files.
         *
         * If null, the icons will be cached indefinitely.
         *
         * @var int|null
         */
        public ?int $cacheDuration = null,

        /**
         * URL of the Iconify API.
         *
         * This allows you to switch to a local or self-hosted Iconify API.
         *
         * @var string
         */
        public string $iconifyApiUrl = 'https://api.iconify.design',
    ) {
    }
}
