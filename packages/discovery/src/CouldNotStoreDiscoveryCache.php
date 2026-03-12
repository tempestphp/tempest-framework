<?php

namespace Tempest\Discovery;

use Exception;

final class CouldNotStoreDiscoveryCache extends Exception
{
    public function __construct(DiscoveryLocation $location)
    {
        parent::__construct(sprintf(
            'Could not store discovery cache for %s. This is likely because you\'re trying to store unserializable data like reflection classes or closures in discovery items.',
            $location->path,
        ));
    }
}
