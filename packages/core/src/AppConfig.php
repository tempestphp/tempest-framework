<?php

declare(strict_types=1);

namespace Tempest\Core;

use Tempest\Core\Exceptions\LogExceptionProcessor;

use function Tempest\env;

final class AppConfig
{
    public Environment $environment;

    public string $baseUri;

    public function __construct(
        ?Environment $environment = null,
        ?string $baseUri = null,

        /** @var class-string<\Tempest\Core\ExceptionProcessor>[] */
        public array $exceptionProcessors = [],
    ) {
        $this->environment = $environment ?? Environment::fromEnv();

        $this->baseUri = $baseUri ?? env('BASE_URI') ?? '';
    }
}
