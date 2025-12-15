<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\env;

final class AppConfig
{
    public string $baseUri;

    public function __construct(
        public ?string $name = null,

        ?string $baseUri = null,

        /**
         * @var array<class-string<\Tempest\Core\InsightsProvider>>
         */
        public array $insightsProviders = [],
    ) {
        $this->baseUri = $baseUri ?? env('BASE_URI') ?? '';
    }
}
