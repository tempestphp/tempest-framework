<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class ViewComponentElement implements Element
{
    use IsElement;

    public function __construct(
        private ViewComponent $viewComponent,
        private ?Element $previous,
        private array $attributes,
    ) {}

    public function render(ViewRenderer $renderer): string
    {
        return $this->viewComponent->render($renderer);
    }
}