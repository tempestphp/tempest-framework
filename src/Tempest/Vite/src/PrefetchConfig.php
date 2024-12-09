<?php

declare(strict_types=1);

namespace Tempest\Vite;

final class PrefetchConfig
{
    public function __construct(
        /**
         * Strategy for prefetching assets at runtime.
         */
        public PrefetchStrategy $strategy = PrefetchStrategy::NONE,

        /**
         * Number of assets to prefech concurrently when using the waterfall strategy.
         */
        public int $concurrent = 3,

        /**
         * Name of the event that triggers prefetching.
         */
        public string $prefetchEvent = 'load',
    ) {
    }
}
