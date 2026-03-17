<?php

use Tempest\Core\DiscoveryCacheStrategy;

final class DiscoveryCacheStrategyNamespaceChange
{
    public function getStrategy(): DiscoveryCacheStrategy
    {
        return DiscoveryCacheStrategy::Full;
    }
}
