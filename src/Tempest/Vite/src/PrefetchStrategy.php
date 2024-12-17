<?php

declare(strict_types=1);

namespace Tempest\Vite;

enum PrefetchStrategy
{
    /**
     * Prefetching is disabled.
     */
    case NONE;

    /**
     * Eagerly prefetch JavaScript and CSS assets by injecting a script that loads them by batch.
     */
    case WATERFALL;

    /**
     * Eagerly prefetch JavaScript and CSS assets by injecting a script that loads them all at once.
     */
    case AGGRESSIVE;
}
