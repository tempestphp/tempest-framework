<?php

namespace Tempest\Discovery;

use Exception;

final class RegistryWasNotSet extends Exception
{
    public function __construct()
    {
        parent::__construct("No registry was set, did you forget to call `DiscoveryDiscovery::setRegistry()`?");
    }
}