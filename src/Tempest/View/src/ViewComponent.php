<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\View\Elements\GenericElement;

interface ViewComponent
{
    public static function getName(): string;

    public function render(GenericElement $element, ViewRenderer $renderer): string;
}
