<?php

declare(strict_types=1);

namespace Tempest\Console;

use Generator;

interface InteractiveComponent
{
    public function render(): Generator|string;

    public function renderFooter(): string;
}
