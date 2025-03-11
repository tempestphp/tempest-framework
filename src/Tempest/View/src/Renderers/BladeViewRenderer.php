<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Tempest\Blade\Blade;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

final readonly class BladeViewRenderer implements ViewRenderer
{
    public function __construct(
        private Blade $blade,
    ) {
    }

    public function render(View|string|null $view): string
    {
        return $this->blade->render($view->path, $view->data);
    }
}
