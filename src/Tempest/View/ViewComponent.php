<?php

declare(strict_types=1);

namespace Tempest\View;

interface ViewComponent
{
    public function getName(): string;

    public function render(Element $element): string;
}
