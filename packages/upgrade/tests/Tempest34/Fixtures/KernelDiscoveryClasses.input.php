<?php

use Tempest\Core\Kernel;

final class KernelDiscoveryClasses
{
    public function __construct(
        private Kernel $kernel,
    ) {}

    public function getClasses(): array
    {
        return $this->kernel->discoveryClasses;
    }
}
