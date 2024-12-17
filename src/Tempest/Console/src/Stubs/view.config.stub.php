<?php

declare(strict_types=1);

use Tempest\View\Renderers\TwigViewRenderer;
use Tempest\View\ViewConfig;

return new ViewConfig(
    rendererClass: TwigViewRenderer::class,
);
