<?php

declare(strict_types=1);

namespace Tempest\Console;

interface StaticConsoleComponent
{
    public function render(Console $console): mixed;
}
