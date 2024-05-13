<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Tempest\Console\Console;

interface StaticComponent
{
    public function render(Console $console): mixed;
}
