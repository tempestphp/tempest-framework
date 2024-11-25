<?php

declare(strict_types=1);

namespace Tempest\Framework\EventHandlers;

use Tempest\Cache\CacheConfig;
use Tempest\Core\Commands\GenerateDiscovery;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\KernelEvent;
use Tempest\EventBus\EventHandler;

final readonly class DiscoveryCacheHandler
{
    public function __construct(
        private CacheConfig $cacheConfig,
        private DiscoveryCache $discoveryCache,
        private GenerateDiscovery $generateDiscovery,
    ) {
    }

    #[EventHandler(KernelEvent::BOOTED)]
    public function __invoke(): void
    {
        if ($this->discoveryCache->isValid()) {
            return;
        }

        ($this->generateDiscovery)();

        $this->cacheConfig->discoveryCache = $this->cacheConfig->resolveDiscoveryCacheStrategy();
    }
}
