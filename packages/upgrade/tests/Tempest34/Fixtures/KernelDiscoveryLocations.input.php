<?php

use Tempest\Core\Kernel;

final class KernelDiscoveryLocations
{
    public function __construct(
        private Kernel $kernel,
    ) {}

    public function getLocations(): array
    {
        return $this->kernel->discoveryLocations;
    }
}
