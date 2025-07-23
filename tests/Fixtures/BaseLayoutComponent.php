<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentParameters;

final readonly class BaseLayoutComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-base-layout';
    }

    public static function getParameters(): ViewComponentParameters
    {
        return new ViewComponentParameters();
    }

    public function compile(ViewComponentElement $element): string
    {
        return <<<HTML
        <div class="base">
            <x-slot />
        </div>
        HTML;
    }
}
