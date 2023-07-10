<?php

namespace Tempest;

final readonly class AppConfig
{
    public function __construct(
        public string $rootPath,
    ) {}
}