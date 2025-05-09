<?php

namespace Tempest\Core;

final class DiscoveryConfig
{
    public function __construct(
        public array $skipDiscovery = [],
    ) {}
}
