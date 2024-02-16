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
        public array $packages = [
            new TempestPackage(),
        ],
    ) {
    }

    public function withPackages(Package ...$packages): self
    {
        $this->packages = [...$this->packages, ...$packages];

        return $this;
    }
}
