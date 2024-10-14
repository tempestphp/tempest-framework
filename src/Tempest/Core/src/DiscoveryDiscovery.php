<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Container\Container;
use Tempest\Reflection\ClassReflector;

final readonly class DiscoveryDiscovery implements Discovery
{
    public function __construct(
        private Kernel $kernel,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        if ($class->getName() === self::class) {
            return;
        }

        if (! $class->implements(Discovery::class)) {
            return;
        }

        $this->kernel->discoveryClasses[] = $class->getName();
    }

    public function createCachePayload(): string
    {
        return serialize($this->kernel->discoveryClasses);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $discoveryClasses = unserialize($payload, [
            'allowed_classes' => true,
        ]);

        $this->kernel->discoveryClasses = $discoveryClasses;
    }
}
