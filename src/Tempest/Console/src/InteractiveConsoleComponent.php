<?php

declare(strict_types=1);

namespace Tempest\Console;

use Generator;

interface InteractiveConsoleComponent
{
    public function render(): Generator|string;

    public function renderFooter(): string;
}
