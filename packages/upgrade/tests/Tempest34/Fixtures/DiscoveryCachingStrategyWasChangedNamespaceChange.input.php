<?php

use Tempest\Core\DiscoveryCachingStrategyWasChanged;

final class DiscoveryCachingStrategyWasChangedNamespaceChange
{
    public function handle(): void
    {
        throw new DiscoveryCachingStrategyWasChanged();
    }
}
