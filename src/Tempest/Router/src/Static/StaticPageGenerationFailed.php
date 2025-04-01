<?php

namespace Tempest\Router\Static;

use Throwable;

final readonly class StaticPageGenerationFailed
{
    public function __construct(
        public string $path,
        public Throwable $exception,
    ) {}
}
