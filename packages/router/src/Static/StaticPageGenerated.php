<?php

namespace Tempest\Router\Static;

final readonly class StaticPageGenerated
{
    public function __construct(
        public string $uri,
        public string $path,
        public string $content,
    ) {}
}
