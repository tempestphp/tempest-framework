<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\GenericElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class ComplexBaseLayoutComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-complex-base';
    }

    public function render(GenericElement $element, ViewRenderer $renderer): string
    {
        return <<<HTML
        <x-slot name="scripts" />
        
        <div class="base">
            <x-slot />
        </div>

        <x-slot name="styles" />
        HTML;
    }
}
