<?php

namespace Tempest\Discovery;

use Exception;

final class DiscoveryCacheStrategyCouldNotBeDeterminedWithEnvironment extends Exception
{
    public function __construct()
    {
        parent::__construct(
            'Could not determine discovery caching strategy based on environment. Require `tempest/core` if you want to use `DiscoveryCacheStrategy::resolveFromEnvironment()`',
        );
    }
}
