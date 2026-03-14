<?php

use Tempest\Core\DiscoveryCache;

final class DiscoveryCacheNamespaceChange
{
    public function __construct(
        private DiscoveryCache $cache,
    ) {}
}
