<?php

declare(strict_types=1);

namespace Tempest\Container;

use Tempest\Core\Discovery;
use Tempest\Core\HandlesDiscoveryCache;
use Tempest\Reflection\ClassReflector;

/**
 * @property GenericContainer $container
 */
final class TaggedIteratorDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private readonly Container $container,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        $tags = $class->getAttributes(Tagged::class);
        foreach ($tags as $tag) {
            $this->container->tag($tag->name, $class->getName());
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->container->getTaggedDefinitions());
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $this->container->setTaggedDefinitions(unserialize($payload));
    }
}
