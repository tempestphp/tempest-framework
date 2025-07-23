<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewComponentParameter;
use Tempest\View\ViewComponentParameters;

final readonly class MyViewComponent implements ViewComponent
{
    public static function getName(): string
    {
        return 'my';
    }

    public static function getParameters(): ViewComponentParameters
    {
        return new ViewComponentParameters(
            new ViewComponentParameter(
                name: 'foo',
                required: false,
            ),
            new ViewComponentParameter(
                name: 'bar',
                required: false,
            ),
        );
    }

    public function compile(ViewComponentElement $element): string
    {
        $foo = $element->getAttribute('foo');
        $bar = $element->getAttribute('bar');

        if ($foo && $bar) {
            return "<div foo=\"{$foo}\" bar=\"{$bar}\"><x-slot /></div>";
        }

        return '<div><x-slot /></div>';
    }
}
