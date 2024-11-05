<?php

declare(strict_types=1);

namespace Tempest\View;

interface ViewRenderer
{
    public function render(string|View $view): string;
}
