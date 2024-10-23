<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Console\PublishConfig;
use Tempest\Container\Container;
use Tempest\Reflection\ClassReflector;

final readonly class PublishDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private PublishConfig $publish,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        if ($class->getName() === self::class) {
            return;
        }

        if (! $class->hasAttribute(CanBePublished::class)) {
            return;
        }

        $this->publish->publishClasses[] = $class->getName();
    }

    public function createCachePayload(): string
    {
        return serialize(
            [
                'publish_classes' => $this->publish->publishClasses,
                'publish_files' => $this->publish->publishFiles,
            ],
        );
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $data = unserialize($payload);

        $this->publish->publishClasses = $data['publish_classes'] ?? [];
        $this->publish->publishFiles = $data['publish_files'] ?? [];
    }
}
