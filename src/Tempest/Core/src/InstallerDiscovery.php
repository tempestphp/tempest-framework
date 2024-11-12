<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Reflection\ClassReflector;

final readonly class InstallerDiscovery implements Discovery
{
    public function __construct(
        private InstallerConfig $installerConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        if ($class->implements(Installer::class)) {
            $this->installerConfig->installers[] = $class->getName();
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->installerConfig->installers);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $this->installerConfig->installers = unserialize($payload);
    }
}
