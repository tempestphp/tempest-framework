<?php

declare(strict_types=1);

namespace Tempest\View;

use Tempest\View\Elements\ViewComponentElement;

interface ViewComponent
{
    public static function getName(): string;

    public function compile(ViewComponentElement $element): string;
}
