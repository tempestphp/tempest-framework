<?php

declare(strict_types=1);

namespace Tempest\Http\Static;

use Tempest\Container\Container;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\HandlesDiscoveryCache;
use Tempest\Http\StaticPage;
use Tempest\Support\Reflection\ClassReflector;

final readonly class StaticPageDiscovery implements Discovery
{
    use HandlesDiscoveryCache;

    public function __construct(
        private StaticPageConfig $staticPageConfig,
    ) {
    }

    public function discover(ClassReflector $class): void
    {
        foreach ($class->getPublicMethods() as $method) {
            $staticPage = $method->getAttribute(StaticPage::class);

            if (! $staticPage) {
                continue;
            }

            $this->staticPageConfig->addHandler($staticPage, $method);
        }
    }

    public function createCachePayload(): string
    {
        return serialize($this->staticPageConfig->staticPages);
    }

    public function restoreCachePayload(Container $container, string $payload): void
    {
        $this->staticPageConfig->staticPages = unserialize($payload);
    }
}
