<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;

final readonly class DefaultSlotValueComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-default-slot';
    }

    public function compile(ViewComponentElement $element): string
    {
        return <<<HTML
        <div>
            <x-slot>Default Value</x-slot>
        </div>
        HTML;
    }
}
