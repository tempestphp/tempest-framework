<?php

declare(strict_types=1);

namespace Tempest\View;

interface ViewComponent
{
    public static function getName(): string;

    public function render(ViewRenderer $renderer, Element $element): string;
}
