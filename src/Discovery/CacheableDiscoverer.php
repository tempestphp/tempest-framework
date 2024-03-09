<?php

declare(strict_types=1);

namespace Tempest\Discovery;

interface CacheableDiscoverer
{
    /**
     * @return array<array-key,object>
     */
    public function getResults(): array;

    /**
     * @param array<array-key,object> $classes
     * @return void
     */
    public function restoreResults(array $classes): void;
}
