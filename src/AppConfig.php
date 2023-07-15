<?php

declare(strict_types=1);

namespace Tempest;

final readonly class AppConfig
{
    public function __construct(
        public string $rootPath,
    ) {
    }
}
