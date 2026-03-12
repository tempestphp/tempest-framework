<?php

declare(strict_types=1);

namespace Tempest\Vite;

use Tempest\Vite\Vite;

use function Tempest\Container\get;

/**
 * Gets tags for the specified or configured `$entrypoints`.
 */
function get_tags(null|string|array $entrypoints = null): array
{
    return get(Vite::class)->getTags(is_array($entrypoints) ? $entrypoints : [$entrypoints]);
}
