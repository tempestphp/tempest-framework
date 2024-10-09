<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\View\Elements\ViewComponentElement;
use Tempest\View\ViewComponent;
use Tempest\View\ViewRenderer;

final readonly class MyViewComponentWithInjection implements ViewComponent
{
    public static function getName(): string
    {
        return 'x-with-injection';
    }

    public function __construct()
    {
    }

    public function compile(ViewComponentElement $element): string
    {
        return 'hi';
    }
}
