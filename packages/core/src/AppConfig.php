<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\env;

final class AppConfig
{
    public Environment $environment;

    public string $baseUri;

    public string $name;

    /** @var array<class-string<\Tempest\Core\InsightsProvider>> */
    public array $insightsProviders = [];

    public function __construct(
        ?string $name = null,
        ?Environment $environment = null,
        ?string $baseUri = null,
    ) {
        $this->name = $name ?: env('NAME') ?? 'tempest';
        $this->environment = $environment ?? Environment::fromEnv();
        $this->baseUri = $baseUri ?: env('BASE_URI') ?? '';
    }
}
