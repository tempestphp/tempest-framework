<?php

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\GenericElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class BaseLayoutComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-base';
    }

    public function render(GenericElement $element, ViewRenderer $renderer): string
    {
        return <<<HTML
        <div class="base">
            <x-slot />
        </div>
        HTML;
    }
}