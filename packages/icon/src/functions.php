<?php

namespace Tempest\Icon;

use function Tempest\get;

/**
 * Renders an icon as an SVG snippet. If the icon is not cached, it will be
 * downloaded on the fly and cached it for future use. If the icon is
 * already in the cache, it will be served from there.
 *
 * This function may return `null` if an error occurred in the rendering process.
 */
function render(string $icon): ?string
{
    return get(Icon::class)->render($icon);
}
