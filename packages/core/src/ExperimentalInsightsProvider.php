<?php

namespace Tempest\Core;

use function Tempest\Support\arr;

final class ExperimentalInsightsProvider implements InsightsProvider
{
    public string $name = 'Experimental features';

    public function __construct(
        private readonly ExperimentalConfig $experimentalConfig,
    ) {}

    public function getInsights(): array
    {
        return arr($this->experimentalConfig->experimentalFeatures)
            ->mapWithKeys(fn (Experimental $experimental) => yield $experimental->name => new Insight('EXPERIMENTAL', Insight::WARNING))
            ->toArray();
    }
}
