<?php

namespace Tempest\Core;

final class DiscoveryConfig
{
    public function __construct(
        public array $skipDiscovery = [],
    ) {}

    public function shouldBeSkipped(string $pathOrClassName): bool
    {
        return in_array($pathOrClassName, $this->skipDiscovery);
    }
}
