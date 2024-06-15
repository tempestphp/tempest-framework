<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewRenderer;

final readonly class EmptyElement implements Element
{
    use IsElement;

    public function render(ViewRenderer $renderer): string
    {
        return '';
    }
}