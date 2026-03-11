<?php

namespace Tempest\Discovery;

final class Registry
{
    public function __construct(
        /** @var \Tempest\Discovery\DiscoveryLocation[] */
        public array $locations = [],

        /** @var array<array-key, class-string<\Tempest\Discovery\Discovery>> */
        public array $classes = [],
    ) {}
}
