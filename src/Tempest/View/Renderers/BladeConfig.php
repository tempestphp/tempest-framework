<?php

namespace Tempest\View\Renderers;

final readonly class BladeConfig
{
    public function __construct(
        public array $viewPaths = [],
        public ?string $cachePath = null,
    ) {}
}