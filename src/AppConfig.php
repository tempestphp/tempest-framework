<?php

declare(strict_types=1);

namespace Tempest;

use Tempest\Application\Environment;

final readonly class AppConfig
{
    public function __construct(
        public string $appPath,
        public string $appNamespace,
        public Environment $environment = Environment::LOCAL,
        public bool $discoveryCache = false,
    ) {
    }
}
