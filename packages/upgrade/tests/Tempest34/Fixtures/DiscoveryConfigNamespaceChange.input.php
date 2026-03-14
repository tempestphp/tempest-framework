<?php

use Tempest\Core\DiscoveryConfig;

final class DiscoveryConfigNamespaceChange
{
    public function __construct(
        private DiscoveryConfig $config,
    ) {}
}
