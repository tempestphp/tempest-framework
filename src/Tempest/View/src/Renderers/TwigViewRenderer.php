<?php

declare(strict_types=1);

namespace Tempest\View\Renderers;

use Tempest\View\View;
use Tempest\View\ViewRenderer;
use Twig\Environment;

final readonly class TwigViewRenderer implements ViewRenderer
{
    public function __construct(
        private Environment $twig,
    ) {
    }

    public function render(View|string|null $view): string
    {
        if ($view === null) {
            return '';
        }

        return trim($this->twig->render($view->path, $view->data));
    }
}
