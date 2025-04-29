<?php

namespace Tempest\Router\Static;

final readonly class StaticPageRemoved
{
    public function __construct(
        public string $path,
    ) {}
}
