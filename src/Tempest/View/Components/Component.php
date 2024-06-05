<?php

namespace Tempest\View\Components;

use Tempest\View\ViewComponent;

final readonly class Component implements ViewComponent
{
    public function __construct(
        private string $name,
    ) {}

    public static function getName(): string
    {
        return 'x-component';
    }

    public function render(string $slot): string
    {
        return $slot;
    }
}