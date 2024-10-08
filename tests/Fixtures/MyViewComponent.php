<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\GenericElement;
use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class MyViewComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'my';
    }

    public function render(ViewComponentElement $element, ViewRenderer $renderer): string
    {
        $foo = $element->getAttribute('foo');
        $bar = $element->getAttribute('bar');

        if ($foo && $bar) {
            return "<div foo=\"{$foo}\" bar=\"{$bar}\"><x-slot /></div>";
        }

        return '<div><x-slot /></div>';
    }
}
