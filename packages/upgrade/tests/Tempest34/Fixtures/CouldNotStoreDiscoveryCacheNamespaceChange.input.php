<?php

namespace Tempest\Upgrade\Tests\Tempest34\Fixtures;

use Tempest\Core\CouldNotStoreDiscoveryCache;

final class CouldNotStoreDiscoveryCacheNamespaceChange
{
    public function handle(): void
    {
        throw new CouldNotStoreDiscoveryCache();
    }
}
