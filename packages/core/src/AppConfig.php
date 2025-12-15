<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\env;

final class AppConfig
{
    public Environment $environment;

    public string $baseUri;

    public function __construct(
        public ?string $name = null,

        ?Environment $environment = null,

        ?string $baseUri = null,

        /**
         * @var array<class-string<\Tempest\Core\InsightsProvider>>
         */
        public array $insightsProviders = [],
    ) {
        $this->environment = $environment ?? Environment::fromEnv();
        $this->baseUri = $baseUri ?? env('BASE_URI') ?? '';
    }
}
