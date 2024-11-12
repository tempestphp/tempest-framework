<?php

declare(strict_types=1);

namespace Tempest\Core;

final readonly class DiscoveryLocation
{
    public function __construct(
        public string $namespace,
        public string $path,
    ) {
    }

    public function isVendor(): bool
    {
        return str_contains($this->path, '/vendor/')
            || str_contains($this->path, '\\vendor\\');
    }
}
