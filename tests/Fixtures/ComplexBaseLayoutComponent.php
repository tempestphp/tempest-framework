<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final readonly class ComplexBaseLayoutComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-complex-base';
    }

    public function compile(ViewComponentElement $element): string
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
