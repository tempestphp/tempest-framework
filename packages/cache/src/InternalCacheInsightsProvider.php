<?php

namespace Tempest\Cache;

use Tempest\Core\ConfigCache;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\Insight;
use Tempest\Core\InsightsProvider;
use Tempest\Icon\IconCache;
use Tempest\View\ViewCache;

final class InternalCacheInsightsProvider implements InsightsProvider
{
    public string $name = 'Internal caches';

    public function __construct(
        private readonly ConfigCache $configCache,
        private readonly ViewCache $viewCache,
        private readonly IconCache $iconCache,
        private readonly DiscoveryCache $discoveryCache,
    ) {}

    public function getInsights(): array
    {
        return [
            'Discovery' => match ($this->discoveryCache->valid) {
                false => new Insight('Invalid', Insight::ERROR),
                true => match ($this->discoveryCache->enabled) {
                    true => new Insight('Enabled', Insight::ERROR),
                    false => new Insight('Disabled', Insight::WARNING),
                },
            },
            'Configuration' => $this->getInsight($this->configCache->enabled),
            'View' => $this->getInsight($this->viewCache->enabled),
            'Icon' => $this->getInsight($this->iconCache->enabled),
        ];
    }

    private function getInsight(bool $enabled): Insight
    {
        if ($enabled) {
            return new Insight('ENABLED', Insight::SUCCESS);
        }

        return new Insight('DISABLED', Insight::WARNING);
    }
}
