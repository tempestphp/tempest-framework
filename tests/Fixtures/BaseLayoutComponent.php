<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class BaseLayoutComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-base-layout';
    }

    public function render(ViewComponentElement $element, ViewRenderer $renderer): string
    {
        return <<<HTML
        <div class="base">
            <x-slot />
        </div>
        HTML;
    }
}
