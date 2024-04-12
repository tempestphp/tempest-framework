<?php

declare(strict_types=1);

namespace Tempest\Console;

use Generator;

interface ConsoleComponent
{
    public function render(): Generator|string;
}
