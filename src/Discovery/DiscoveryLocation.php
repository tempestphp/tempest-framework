<?php

namespace Tempest\Discovery;

final readonly class DiscoveryLocation
{
    public function __construct(
        public string $namespace,
        public string $path,
    ) {}
}