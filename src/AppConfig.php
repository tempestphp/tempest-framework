<?php

declare(strict_types=1);

namespace Tempest;

use Tempest\Application\Environment;
use Tempest\Interface\Package;

final class AppConfig
{
    public function __construct(
        public Environment $environment = Environment::LOCAL,
        public bool $discoveryCache = false,
        /** @var \Tempest\Discovery\DiscoveryLocation[] */
        public array $discoveryLocations = [],
    ) {
    }

    public function withPackages(Package ...$packages): self
    {
        $this->discoveryLocations = [...$this->discoveryLocations, ...$packages];

        return $this;
    }
}
