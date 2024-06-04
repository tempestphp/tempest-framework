<?php

declare(strict_types=1);

namespace Tempest\Discovery;

final readonly class DiscoveryLocation
{
    public function __construct(
        public string $namespace,
        public string $path,
    ) {
    }
}
