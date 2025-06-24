<?php

namespace Tempest\Intl;

use Tempest\Core\Insight;
use Tempest\Core\InsightsProvider;

use function Tempest\Support\arr;

final class IntlInsightsProvider implements InsightsProvider
{
    public string $name = 'Locale';

    public function __construct(
        private readonly IntlConfig $intlConfig,
    ) {}

    public function getInsights(): array
    {
        return [
            'Current locale' => $this->intlConfig->currentLocale->getDisplayLanguage(),
            'Fallback locale' => $this->intlConfig->fallbackLocale->getDisplayLanguage(),
            'Translation files' => (string) arr($this->intlConfig->translationMessagePaths)->flatten()->count(),
            'Intl extension' => extension_loaded('intl') ? new Insight('ENABLED', Insight::SUCCESS) : new Insight('DISABLED', Insight::WARNING),
        ];
    }
}
