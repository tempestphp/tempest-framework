<?php

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\View;
use Tempest\View\ViewRenderer;

final class EmptyElement implements Element
{
    use IsElement;

    public function __construct(
        private readonly Element $original,
    ) {}

    public function render(ViewRenderer $renderer): string
    {
        return '';
    }
}