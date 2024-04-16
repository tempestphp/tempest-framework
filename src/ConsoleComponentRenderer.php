<?php

declare(strict_types=1);

namespace Tempest\Console;

interface ConsoleComponentRenderer
{
    public function render(ConsoleComponent $component): mixed;
}
