<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Reflection\ClassReflector;

final class InstallerDiscovery implements Discovery
{
    use IsDiscovery;

    public function __construct(
        private readonly InstallerConfig $installerConfig,
    ) {
    }

    public function discover(DiscoveryLocation $location, ClassReflector $class): void
    {
        if ($class->implements(Installer::class)) {
            $this->discoveryItems->add($location, $class->getName());
        }
    }

    public function apply(): void
    {
        foreach ($this->discoveryItems as $className) {
            $this->installerConfig->installers[] = $className;
        }
    }
}
