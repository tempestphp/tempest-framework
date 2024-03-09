<?php

declare(strict_types=1);

namespace Tempest\Discovery;

class DiscoveryService
{
    private array $discoverers = [];

    public function addDiscoverer(Discovery $discoverer): self
    {
        $this->discoverers[] = $discoverer;

        return $this;
    }

    public function discover(): void
    {

    }
}
