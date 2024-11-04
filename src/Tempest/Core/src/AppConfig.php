<?php

declare(strict_types=1);

namespace Tempest\Core;

use function Tempest\env;

final class AppConfig
{
    public Environment $environment;

    public string $baseUri;

    public function __construct(
        ?Environment $environment = null,
        ?string $baseUri = null,

        /** @var \Tempest\Core\ErrorHandler[] */
        public array $errorHandlers = [
            // …,
        ],
    ) {
        $this->environment = $environment
            ?? Environment::tryFrom(env('ENVIRONMENT', 'local'))
            ?? Environment::LOCAL;

        $this->baseUri = $baseUri ?? env('BASE_URI') ?? '';
    }
}
