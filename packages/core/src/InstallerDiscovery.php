<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Discovery\IsDiscovery;
use Tempest\Reflection\ClassReflector;

final class InstallerDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly InstallerConfig $installerConfig,
    ) {}

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $installer = $method->getAttribute(Installer::class);

            if (! $installer) {
                continue;
            }

            $this->discoveryItems->add($location, [$method, $installer]);
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as [$method, $installer]) {
            $this->installerConfig->addInstaller($method, $installer);
        }
    }
}
