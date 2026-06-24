<?php

declare(strict_types=1);

namespace Tempest\Vite;

use function Tempest\Container\get;

/**
 * Gets tags for the specified or configured `$entrypoints`.
 */
function get_tags(string|array|null $entrypoints = null): array
{
    return get(Vite::class)->getTags(is_array($entrypoints) ? $entrypoints : [$entrypoints]);
}
