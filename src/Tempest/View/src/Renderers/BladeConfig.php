<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

final readonly class BladeConfig
{
    public function __construct(
        public array $viewPaths = [],
        public ?string $cachePath = null,
    ) {
    }
}
