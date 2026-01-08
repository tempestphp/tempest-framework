<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\env;

final class AppConfig
{
    public string $baseUri;

    /** @var array<class-string<\Tempest\Core\InsightsProvider>> */
    public array $insightsProviders = [];

    public function __construct(
        public ?string $name = null,
        ?string $baseUri = null,
    ) {
        $this->baseUri = $baseUri ?: env('BASE_URI', default: '');
    }
}
