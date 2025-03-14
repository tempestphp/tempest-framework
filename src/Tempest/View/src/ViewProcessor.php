<?php

declare(strict_types=1);

namespace Tempest\View;

interface ViewProcessor
{
    public function process(View $view): View;
}
