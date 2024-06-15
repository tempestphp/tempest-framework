<?php

namespace Tempest\View\Components;

use Tempest\View\View;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;
use function Tempest\view;

final readonly class Component implements ViewComponent
{
    public function __construct(
        private string $name,
        private string $view,
        private ?View $slot = null,
    ) {}

    public static function getName(): string
    {
        return 'x-component';
    }

    public function render(ViewRenderer $renderer): string
    {
        return $renderer->render(view($this->view)->data(slot: $this->slot));
    }
}