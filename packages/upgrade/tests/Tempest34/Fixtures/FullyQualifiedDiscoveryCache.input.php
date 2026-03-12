<?php

final class FullyQualifiedDiscoveryCache
{
    public function __construct(
        private \Tempest\Core\DiscoveryCache $cache,
    ) {}
}
