<?php

declare(strict_types=1);

namespace Tempest\Core;

final class AppConfig
{
    public Environment $environment;

    public string $baseUri;

    public function __construct(
        ?Environment $environment = null,
        ?string $baseUri = null,
        public ExceptionHandlerSetup $exceptionHandlerSetup = new GenericExceptionHandlerSetup(),

        /** @var \Tempest\Core\ExceptionHandler[] */
        public array $exceptionHandlers = [
            // …,
        ],
    ) {
        $this->environment = $environment
            ?? Environment::tryFrom(\Tempest\env('ENVIRONMENT', 'local'))
            ?? Environment::LOCAL;

        $this->baseUri = $baseUri ?? \Tempest\env('BASE_URI') ?? '';
    }
}
