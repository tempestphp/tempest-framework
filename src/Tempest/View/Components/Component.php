<?php

namespace Tempest\View\Components;

use Tempest\View\View;
use Tempest\View\ViewComponent;
use function Tempest\view;

final readonly class Component implements ViewComponent
{
    public function __construct(
        private string $name,
        private string $view,
    ) {}

    public static function getName(): string
    {
        return 'x-component';
    }

    public function render(string $slot): string|View
    {
        return view($this->view)->data(slot: $slot);
    }
}