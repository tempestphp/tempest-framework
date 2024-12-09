<?php

declare(strict_types=1);

namespace Tempest\Vite;

enum PrefetchStrategy
{
    /**
     * Prefetching is disabled.
     */
    case NONE;

    case WATERFALL; // TODO: comment

    case AGGRESSIVE; // TODO: comment
}
