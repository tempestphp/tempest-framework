<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Generator;

interface InteractiveComponent
{
    public function render(): Generator|string;
}
