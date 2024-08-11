<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures;

use Tempest\Http\Session\Session;
use Tempest\View\Elements\GenericElement;
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

    public function render(GenericElement $element, ViewRenderer $renderer): string
    {
        return 'hi';
    }
}
