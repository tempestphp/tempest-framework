<?php

declare(strict_types=1);

namespace Tempest\View\Elements;

use Tempest\View\Element;
use Tempest\View\ViewRenderer;

final class EmptyElement implements Element
{
    use IsElement;

    public function render(ViewRenderer $renderer): string
    {
        return '';
    }
}
