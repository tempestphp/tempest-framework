<?php

declare(strict_types=1);

namespace Tempest\Console\Components;

use Exception;
use Tempest\Console\Console;
use Tempest\Console\ConsoleComponent;

final class UnsupportedComponentRenderer implements ComponentRenderer
{
    public function render(
        Console $console,
        ConsoleComponent $component,
        array $validation = []
    ): mixed {
        throw new Exception('Unsupported');
    }
}
